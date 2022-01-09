<?php

namespace addons\unidrink\behavior;

use addons\unidrink\extend\Ali;
use addons\unidrink\extend\Common;
use addons\unidrink\extend\Snowflake;
use addons\unidrink\extend\Wechat;
use addons\unidrink\model\Address;
use addons\unidrink\model\Bill;
use addons\unidrink\model\Config;
use addons\unidrink\model\CouponUser;
use addons\unidrink\model\OrderProduct;
use addons\unidrink\model\Product;
use addons\unidrink\model\Shop;
use addons\unidrink\model\User;
use addons\unidrink\model\UserExtend;
use app\admin\model\unidrink\Coupon;
use app\common\model\MoneyLog;
use think\Db;
use think\Exception;

/**
 * 订单相关行为
 * Class Order
 * @package addons\unidrink\behavior
 */
class Order
{

    /**
     * 检查是否符合创建订单的条件
     * 条件1：商品是否存在
     * 条件2：商品库存情况
     * 条件3：店铺是否营业，是否在配送范围，是否够起送价
     * 条件4：是否使用优惠券，优惠券能否可用
     * @param array $params
     * @param array $extra
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function createOrderBefore(&$params, $extra)
    {

        $specs = explode(',', $extra['spec']);
        foreach ($specs as &$spec) {
            $spec = str_replace('|', ',', $spec);
        }
        $numbers = explode(',', $extra['number']);
        $productIds = explode(',', $extra['product_id']);

        if (count($specs) !== count($numbers) || count($specs) !== count($productIds)) {
            throw new Exception(__('Parameter error'));
        }

        // 订单价格
        $orderPrice = 0;

        // 条件一
        $products = [];
        foreach ($productIds as $key => &$productId) {
            $products[$key] = Db::name('unidrink_product')
                ->where(['id' => $productId, 'switch' => Product::SWITCH_ON])
                ->where('shop_id = ' . Product::ALL_SHOP . ' OR FIND_IN_SET(:shop_id, shop_id)', ['shop_id' => $extra['shop_id']])
                ->lock(Config::isPessimism()) // Todo 是否使用悲观锁
                ->find();
            if (!$products[$key]) {
                throw new Exception(__('There are not exist or Offline'));
            }
        }
        if (count($products) == 0 || count($productIds) != count($products)) {
            throw new Exception(__('There are offline product'));
        }
        // 从购物车下单多个商品时，有同一个商品的不同规格导致减库存问题
        if (count($productIds) > 0) {
            $reduceStock = [];
            foreach ($products as $key => $value) {
                if (!isset($reduceStock[$value['id']])) {
                    $reduceStock[$value['id']] = $numbers[$key];
                } else {
                    $products[$key]['stock'] -= $reduceStock[$value['id']];
                    $reduceStock[$value['id']] += $numbers[$key];
                }
            }
        }

        // 条件二
        foreach ($products as $key => $product) {
            $productInfo = (new \addons\unidrink\extend\Product())->getBaseData($product, $specs[$key] ? $specs[$key] : '');
            if ($productInfo['stock'] < $numbers[$key]) {
                throw new Exception(__('库存不足，剩%s件', $productInfo['stock']));
            }
            $orderPrice = bcadd($orderPrice, bcmul($productInfo['sales_price'], $numbers[$key], 2), 2);
            $baseProductInfo[] = $productInfo;
        }

        // 条件三 店铺是否营业，是否在配送范围,是否够起送价
        $shop = (new Shop)->where(['id' => $extra['shop_id']])->find();
        if (!$shop) {
            throw new Exception(__('店铺不存在'));
        }
        if ($shop['status'] == Shop::STATUS_OFF) {
            throw new Exception(__('店铺已打烊'));
        }
        // 外卖
        $address = (new Address)->where(['id' => $extra['address_id'], 'user_id' => $extra['userId']])->find();
        if ($extra['type'] == \addons\unidrink\model\Order::BUY_TYPE_TAKEOUT) {
            if (!$address) {
                throw new Exception(__('收货地址不存在'));
            }
            if ($shop['min_price'] > $orderPrice) {
                throw new Exception(__('起送价为￥' . $shop['min_price']));
            }
            if ($shop['distance'] == Shop::NO_DELIVERY) {
                throw new Exception(__('此店外卖不配送'));
            } else {
                $distance = Common::getDistanceFromLocation($shop['lat'], $shop['lng'], $address['lat'], $address['lng']);
                if ($shop['distance'] < $distance) {
                    throw new Exception(__('不在配送范围，店铺配送:' . $shop['distance'] . 'km,实际距离:' . $distance . 'km'));
                }
            }
        }

        // 条件四
        if ($extra['coupon_id']) {
            $coupon = CouponUser::get($extra['coupon_id']);
            // 判断是否在使用时间内
            if ($coupon['starttime'] > time() || $coupon['endtime'] < time()) {
                throw new Exception('此优惠券不在使用时间内');
            }
            // 判断至少消费多少钱
            if ($coupon['least'] > $orderPrice) {
                throw new Exception('选中的优惠券不满足使用条件');
            }
            // 判断优惠券类型
            if ($coupon['type'] != 0 && $extra['type'] != $coupon['type']) {
                $typeName = $coupon['type'] == 1 ? '自取' : '外卖';
                throw new Exception('选中的优惠券只能在中' . $typeName . '时候使用用');
            }
            // 判断是否已使用
            if ($coupon['status'] == CouponUser::STATUS_ON) {
                throw new Exception('选中的优惠券已使用');
            }
        } else {
            $coupon = [];
        }

        $params = [$products, $shop, $coupon, $baseProductInfo, $address, $orderPrice, $specs, $numbers];
    }

    /**
     * 创建订单之后
     * 行为一：根据订单减少商品库存 增加"已下单未支付数量"
     * 行为二：发送微信订阅信息
     * @param array $params 商品属性
     * @param array $extra [specNumber] => ['spec1' => 'number1','spec2' => 'number2']
     */
    public function createOrderAfter(&$params, $extra)
    {
        // 行为一
        $key = 0;
        $productExtend = new \addons\unidrink\extend\Product;
        $prefix = \think\Config::get('database.prefix');

        if (Config::isPessimism()) {
            // 悲观锁
            foreach ($extra['specNumber'] as $spec => $number) {
                $result = 0;
                if (is_numeric($spec) && $params[$key]['use_spec'] == Product::SPEC_OFF) {
                    $result = Db::execute('UPDATE ' . $prefix . "unidrink_product SET no_buy_yet = no_buy_yet+{$number}, real_sales = real_sales+{$number}, stock = stock-{$number} WHERE id = {$params[$key]['id']}");
                } else if ($params[$key]['use_spec'] == Product::SPEC_ON) {
                    $info = $productExtend->getBaseData($params[$key], $spec);

                    // mysql<5.7.13时用
                    //if (mysql < 5.7.13) {
                    $spec = str_replace(',', '","', $spec);
                    $search = '"stock":"' . $info['stock'] . '","value":["' . $spec . '"]';
                    $stock = $info['stock'] - $number;
                    $replace = '"stock":\"' . $stock . '\","value":["' . $spec . '"]';
                    $sql = 'UPDATE ' . $prefix . "unidrink_product SET no_buy_yet = no_buy_yet+{$number}, stock = stock-{$number}, real_sales = real_sales+{$number} ,`specTableList` = REPLACE(specTableList,'$search','$replace') WHERE id = {$params[$key]['id']}";
                    $result = Db::execute($sql);
                    //}

                    //下面语句直接操作JSON
                    //if (mysql >= 5.7.13) {
                    //$info['stock'] -= $number;
                    //$result = Db::execute("UPDATE fa_unidrink_product SET no_buy_yet = no_buy_yet+{$number}, real_sales = real_sales+{$number}, stock = stock-{$number},specTableList = JSON_REPLACE(specTableList, '$[{$info['key']}].stock', {$info['stock']}) WHERE id = {$params[$key]['id']}");
                    //}
                }
                if ($result == 0) { // 锁生效
                    throw new Exception('下单失败,请重试');
                }
                $key++;
            }
        } else {
            // 乐观锁
            foreach ($extra['specNumber'] as $spec => $number) {
                $result = 0;
                if (is_numeric($spec) && $params[$key]['use_spec'] == Product::SPEC_OFF) {
                    $result = Db::execute('UPDATE ' . $prefix . "unidrink_product SET no_buy_yet = no_buy_yet+{$number}, real_sales = real_sales+{$number}, stock = stock-{$number} WHERE id = {$params[$key]['id']} AND stock = {$params[$key]['stock']}");
                } else if ($params[$key]['use_spec'] == Product::SPEC_ON) {
                    $info = $productExtend->getBaseData($params[$key], $spec);

                    // mysql<5.7.13时用
                    //if (mysql < 5.7.13) {
                    $spec = str_replace(',', '","', $spec);
                    $search = '"stock":"' . $info['stock'] . '","value":["' . $spec . '"]';
                    $stock = $info['stock'] - $number;
                    $replace = '"stock":\"' . $stock . '\","value":["' . $spec . '"]';
                    $sql = 'UPDATE ' . $prefix . "unidrink_product SET no_buy_yet = no_buy_yet+{$number}, real_sales = real_sales+{$number}, stock = stock-{$number},`specTableList` = REPLACE(specTableList,'$search','$replace') WHERE id = {$params[$key]['id']} AND stock = {$params[$key]['stock']}";
                    $result = Db::execute($sql);
                    //}

                    //下面语句直接操作JSON
                    //if (mysql >= 5.7.13) {
                    //$info['stock'] -= $number;
                    //$result = Db::execute("UPDATE fa_unidrink_product SET no_buy_yet = no_buy_yet+{$number}, real_sales = real_sales+{$number}, stock = stock-{$number},specTableList = JSON_REPLACE(specTableList, '$[{$info['key']}].stock', {$info['stock']}) WHERE id = {$params[$key]['id']} AND stock = {$params[$key]['stock']}");
                    //}
                }
                if ($result == 0) { // 锁生效
                    throw new Exception('下单失败,请重试');
                }
                $key++;
            }
        }


        // 行为二 发送微信订阅信息
        try {
            $app = Wechat::initEasyWechat('miniProgram');
            $tmp = Wechat::getOrderSubMsgFromType($extra['type']);
            $user = UserExtend::get(['user_id' => $extra['userId']]);
            $data = Wechat::subscribeMsgData($tmp['template_data'], [
                '取餐号码' => $extra['numberId'],
                '点餐时间' => date('Y-m-d H:i:s'),
                '订单状态' => '制作中',
                '取餐时间' => date('Y-m-d H:i:s', $extra['gettime']),
                '门店名称' => $extra['shop_name'],
                '订单号' => $extra['out_trade_no'],
                '联系人姓名' => $extra['address']['name'] ?? '',
                '联系人手机号' => $extra['address']['mobile'] ?? '',
                '时间' => date('Y-m-d H:i:s'),
                '地址' => ($extra['address']['address'] ?? '') . ($extra['address']['door_number'] ?? ''),
            ]);
            $app->subscribe_message->send([
                'template_id' => $tmp['template_id'],
                'touser' => $user->openid, // 微信小程序openid
                'page' => 'pages/take-foods/take-foods',
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            //@Db::execute("insert into fa_log (log) values ('{$e->getMessage()}')");
        }

        // More ...
    }

    /**
     * 支付成功
     * 行为一：更改订单的支付状态，更新支付时间。
     * 行为二：更新增加历史消费金额
     * 行为三：添加后台工作台通知。
     * 行为四：用掉优惠券
     * 行为五：打印机打印小票
     * @param $params
     * @param $extra
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function paidSuccess(&$params, $extra)
    {
        if (empty($params)) {
            return;
        }
        // 行为一
        /**
         * @var \addons\unidrink\model\Order $order
         */
        $order = &$params;
        $order->have_paid = time();// 更新支付时间为当前时间
        $order->pay_type = $extra['pay_type'];
        $order->save();

        // 行为二
        (new UserExtend())->where(['user_id' => $order->user_id])->setInc('currentValue', $order->total_price);

        // 行为三
        $shop = Shop::get($order->shop_id);
        if (method_exists(\addons\voicenotice\library\Voicenotice::class, 'addNotice')) {
            if ($shop) {
                \addons\voicenotice\library\Voicenotice::addNotice($shop['name'] . '有一个新订单', $shop['admin_id'], false, false, 'unidrink/workspace/index?ref=addtabs&addtabs=1&shop_id=' . $shop['id'], 'addtabs');
            }
        }

        // 行为四
        $couponId = $order->extend->coupon_id;
        if ($couponId) {
            CouponUser::update([
                'status' => 1
            ], [
                'id' => $couponId
            ]);
        }

        // 行为五
        try {
            if (method_exists(\addons\uniprint\library\printer::class, 'printingTemplate')) {
                $orderType = $order['type'] == 1 ? '客户自提' : '商家配送';
                $orderTime = date('Y-m-d H:i:s', time());
                $address = json_decode($order['extend']['address_json'], true);
                $orderName = $order['type'] == 1 ? '联系人: ' . $order->user->username : '收货人: ' . $address['name'];
                $orderMobile = $order['type'] == 1 ? '联系电话: ' . $order->user->mobile : '收货电话: ' . $address['mobile'];
                $orderAddress = $order['type'] == 1 ? '' : '收货地址: ' . $address['address'] . ' ' . $address['door_number'];
                $deliveryPrice = $order['delivery_price'] > 0 ? '运费:' . $order['delivery_price'] : '';
                $discountPrice = $order['discount_price'] > 0 ? '优惠:' . $order['discount_price'] : '';
                $remark = $order['remark'] ? '备注:'.$order['remark'] : '';
                // 通用打印机模板
                $foods = '';
                foreach ($order->products as $product) {
                    $price = $product['number'] * $product['price'];
                    $price = number_format($price, 2);
                    $foods .= <<<EOP
$product[title]_$product[spec]
$product[number] X $product[price] = $price

EOP;
                }

                $template = <<<EOP
         uniDrink点餐系统
            取餐号:$order[number_id]

订单编号: $order[out_trade_no]
配送方式：$orderType
$orderName
$orderMobile
$orderAddress
下单时间: $orderTime

品名
价格
=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*
$foods
=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*
$remark
$deliveryPrice
$discountPrice
订单金额:$order[order_price]
总计:$order[total_price]
EOP;

                // 如果店铺绑定了指定的打印机
                if (!empty($shop['uniprint_id'])) {
                    $printIds = explode(',', $shop['uniprint_id']);
                    foreach ($printIds as $printId) {
                        \addons\uniprint\library\printer::printingTemplate($template, (int)$printId);
                    }
                } else {
                    // 否则全部打印机一起打印
                    \addons\uniprint\library\printer::printingTemplate($template);
                }
            }
        } catch (\Throwable $e) {
            //  $e->getMessage(); // 打印机错误信息
        }

        // More ...
    }

    /**
     * 支付失败
     * @param $params
     */
    public function paidFail(&$params)
    {
        if (empty($params)) {
            return;
        }
        $order = &$params;
        $order->have_paid = \addons\unidrink\model\Order::PAID_NO;
        $order->save();

        // More ...
    }

    /**
     * 订单退款
     * 行为一：退款
     * 行为一：创建退款账单bill
     * @param array $params 订单数据
     */
    public function orderRefund(&$params)
    {
        /**
         * @var \addons\unidrink\model\Order $order
         */
        $order = &$params;

        // 行为一
        switch ($order['pay_type']) {
            case \addons\unidrink\model\Order::PAY_WXPAY:
                $app = Wechat::initEasyWechat('payment');
                $result = $app->refund->byOutTradeNumber($params['out_trade_no'], $params['out_trade_no'], bcmul($params['total_price'], 100), bcmul($params['total_price'], 100), [
                    // 可在此处传入其他参数，详细参数见微信支付文档
                    'refund_desc' => '普通退款',
                ]);
                break;
            case \addons\unidrink\model\Order::PAY_ALIPAY:
                $alipay = Ali::initAliPay();
                $order = [
                    'out_trade_no' => $params['out_trade_no'],
                    'refund_amount' => $params['total_price'],
                ];
                $result = $alipay->refund($order);
                break;
            case \addons\unidrink\model\Order::PAY_BALANCE:
                $user = (new User())->where(['id' => $order->user_id])->find();
                // 追加用户金额记录
                (new MoneyLog())->save([
                    'user_id' => $order->user_id,
                    'money' => $order->total_price,
                    'before' => $user->money,
                    'after' => $user->money + $order->total_price,
                    'memo' => '退款到余额'
                ]);
                (new User())->where(['id' => $order->user_id])->setInc('money', $params['total_price']);
                break;
        }

        // 行为二创建账单
        (new Bill())->save([
            'user_id' => $order->user_id,
            'out_trade_no' => $order->out_trade_no,
            'type' => Bill::TYPE_REFUND,
            'total_price' => $order->total_price,
            'real_price' => $order->total_price,
            'pay_type' => $order->pay_type,
            'type_id' => $order->id,
            'effecttime' => time()
        ]);

        // More ...

    }
}
