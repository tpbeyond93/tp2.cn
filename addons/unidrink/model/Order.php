<?php

namespace addons\unidrink\model;


use addons\unidrink\extend\Hashids;
use addons\unidrink\extend\Snowflake;
use think\Hook;
use think\Model;
use traits\model\SoftDelete;

/**
 * 收货地址模型
 * Class Favorite
 * @package addons\unidrink\model
 */
class Order extends Model
{
    use SoftDelete;
    protected $deleteTime = 'deletetime';

    // 表名
    protected $name = 'unidrink_order';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 隐藏属性
    protected $hidden = [
        'id',
        'user_id',
    ];

    // 追加属性
    protected $append = [
        'gettime_text'
    ];

    // 支付类型
    const PAY_SCORE = 0; // 积分支付
    const PAY_ONLINE = 1; // 在线支付
    const PAY_OFFLINE = 2; // 线下支付 或 货到付款
    const PAY_WXPAY = 3; // 微信支付
    const PAY_ALIPAY = 4; // 支付宝支付
    const PAY_BALANCE = 5; // 余额支付

    // 订单状态
    const STATUS_NORMAL = 1; // 正常
    const STATUS_CANCEL = 0; // 用户取消订单
    const STATUS_REFUND = -1; // 退款

    // 是否支付
    const PAID_NO = 0; // 否

    // 是否评论
    const COMMENTED_NO = 0; // 否

    // 是否收货
    const RECEIVED_NO = 0; // 否

    // 订单类型
    const TYPE_PAY = 1; // 待付款
    const TYPE_MADE = 2; // 待制作
    const TYPE_RECEIVE = 3; // 待收货
    const TYPE_COMMENT = 4; // 待评价

    // 购买类型:1=自取,2=外卖
    const BUY_TYPE_TAKEIN = 1;
    const BUY_TYPE_TAKEOUT = 2;

    /**
     * 格式化时间
     * @param $value
     * @return false|string
     */
    public function getCreatetimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 格式化时间 madetime
     * @param $value
     * @param $data
     * @return false|string
     */
    public function getMadetimeAttr($value, $data)
    {
        return $data['have_made'] > 0 ? date('Y-m-d H:i:s', $data['have_made']) : '无';
    }

    /**
     * 格式化取餐时间
     * @param $value
     * @param $data
     */
    public function getGettimeTextAttr($value, $data) {
        if ($data['gettime'] == $data['createtime']) {
            return '立即取餐';
        } else {
            return date('H:i', $data['gettime']);
        }
    }

    /**
     * 格式化时间 paidtime
     * @return false|int|string
     */
    public function getPaidtimeAttr($value, $data)
    {
        return $data['have_paid'] > 0 ? date('Y-m-d H:i:s', $data['have_paid']) : 0;
    }

    /**
     * 格式化时间 receivedtime
     * @return false|int|string
     */
    public function getReceivedtimeAttr($value, $data)
    {
        return $data['have_received'] > 0 ? date('Y-m-d H:i:s', $data['have_received']) : 0;
    }

    /**
     * 格式化时间 commentedtime
     * @return false|int|string
     */
    public function getCommentedtimeAttr($value, $data)
    {
        return $data['have_commented'] > 0 ? date('Y-m-d H:i:s', $data['have_commented']) : 0;
    }

    /**
     * 支付类型
     */
    public function getPayTypeTextAttr($value, $data)
    {
        switch ($data['pay_type']) {
            case self::PAY_ONLINE:
                return __('Online');
                break;
            case self::PAY_OFFLINE:
                return __('Offline');
                break;
            case self::PAY_WXPAY:
                return __('wxPay');
                break;
            case self::PAY_ALIPAY:
                return __('aliPay');
                break;
            case self::PAY_BALANCE:
                return __('Balance');
                break;
        }
    }

    /**
     * 享用方式
     */
    public function getTypeTextAttr($value, $data)
    {
        switch ($data['type']) {
            case self::BUY_TYPE_TAKEIN:
                return '自取';
                break;
            case self::BUY_TYPE_TAKEOUT:
                return '外卖';
                break;
        }
    }

    /**
     * 加密订单id
     * @param $value
     * @param $data
     * @return string
     */
    public function getOrderIdAttr($value, $data)
    {
        return Hashids::encodeHex($data['id']);
    }


    /**
     * 创建订单
     * @param $userId
     * @param $data
     * @return int
     * @throws \Exception
     */
    public function createOrder($userId, $data)
    {
        $data['userId'] = $userId;

        Hook::listen('create_order_before', $params, $data);
        list($products, $shop, $coupon, $baseProductInfos, $address, $orderPrice, $specs, $numbers) = $params;

        // 获取雪花算法分布式id，方便以后扩展
        $snowflake = new Snowflake();
        $id = $snowflake->id();
        // 优惠费用
        $discountPrice = isset($coupon['value']) ? $coupon['value'] : 0;
        // 运费
        $deliveryPrice = 0;
        if ($data['type'] == Order::BUY_TYPE_TAKEOUT) {
            $deliveryPrice = $shop['distance'] != Shop::NO_DELIVERY ? $shop['delivery_price'] : 0;
        }
        // 总费用
        $totalPrice = bcadd(bcsub($orderPrice, $discountPrice, 2), $deliveryPrice, 2);

        $out_trade_no = date('Ymd', time()) . uniqid() . $userId;

        // 创建账单
        (new Bill)->createBill($out_trade_no, $userId, $id, $totalPrice, $totalPrice,Bill::TYPE_CONSUME, $data['pay_type']);

        $numberId = OrderNumber::insertGetId(['order_id' => $id]);
        if ($numberId < Config::getByName('order_number')['value']) {
            $numberId = OrderNumber::insertGetId([
                'order_id' => $id,
                'id' => Config::getByName('order_number')['value']
            ]);
        }

        $gettime = ((int)$data['gettime'] * 60) + time(); // 分钟 * 60 + 现在时间戳
        // 创建订单
        (new self)->save([
            'id' => $id,
            'user_id' => $userId,
            'shop_id' => $data['shop_id'],
            'out_trade_no' => $out_trade_no,
            'order_price' => $orderPrice,
            'discount_price' => $discountPrice,
            'delivery_price' => $deliveryPrice,
            'total_price' => $totalPrice,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'remark' => $data['remark'] ?? '',
            'status' => self::STATUS_NORMAL,
            'pay_type' => $data['pay_type'],
            'type' => $data['type'],
            'number_id' => $numberId,
            'gettime' => $gettime,  // 分钟 * 60 + 现在时间戳
        ]);

        (new OrderExtend)->save([
            'user_id' => $userId,
            'order_id' => $id,
            'coupon_id' => $coupon ? $coupon['id'] : 0,
            'coupon_json' => $coupon ? json_encode($coupon) : '',
            'address_id' => $address['id'] ?? 0,
            'address_json' => $address ? json_encode($address) : '',
        ]);


        $orderProduct = $specNumber = [];
        foreach ($products as $key => $product) {
            $orderProduct[] = [
                'user_id' => $userId,
                'order_id' => $id,
                'product_id' => $product['id'],
                'title' => $product['title'],
                'image' => $product['image'],
                'number' => $numbers[$key],
                'spec' => $specs[$key] ?? '',
                'price' => $baseProductInfos[$key]['sales_price'],
                'createtime' => time(),
                'updatetime' => time()
            ];

            if (!empty($specs[$key])) {
                $specNumber[$specs[$key]] = $numbers[$key];
            } else {
                $specNumber[$key] = $numbers[$key];
            }
        }
        (new OrderProduct)->insertAll($orderProduct);

        $data['specNumber'] = $specNumber;
        $data['numberId'] = $numberId;
        $data['gettime'] = $gettime;
        $data['shop_name'] = $shop['name'];
        $data['out_trade_no'] = $out_trade_no;
        $data['address'] = $address ?? [];

        Hook::listen('create_order_after', $products, $data);

        return [
            'order_id' => Hashids::encodeHex($id),
            'out_trade_no' => $out_trade_no
        ];
    }

    /**
     * 获取我的订单
     * @param int $userId 用户id
     */
    public function getOrdersByType($userId, $page = 1, $pageSize = 10)
    {
        $result = $this
            ->with([
                'products' => function ($query) {
                    $query->field('id,title,image,number,price,spec,order_id,product_id');
                },
                'evaluate' => function ($query) {
                    $query->field('id,order_id,product_id');
                },
                'shop' => function($query) {
                    $query->field('id,name');
                }
            ])
            ->where([
                'user_id' => $userId
            ])
            ->where('have_paid', '>' ,0)
            ->order(['createtime' => 'desc'])
            ->page($page, $pageSize)
            ->fetchSql(false)
            ->select();

        foreach ($result as &$item) {
            $item->append(['order_id']);
            $item = $item->toArray();

            $evaluate = array_column($item['evaluate'], 'product_id');
            unset($item['evaluate']);

            foreach ($item['products'] as &$product) {
                $product['image'] = Config::getImagesFullUrl($product['image']);
                // 是否已评论
                if (in_array($product['id'], $evaluate)) {
                    $product['evaluate'] = true;
                } else {
                    $product['evaluate'] = false;
                }
                unset($product['order_id']);
            }

            // 订单状态
            $item['order_status_text'] = $this->orderStatusText($item);
        }

        return $result;
    }

    /**
     * 订单状态
     * @param $order
     */
    public function orderStatusText($order) {
        if ($order['status'] == self::STATUS_REFUND) {
            return '已退款';
        }
        if ($order['status'] == self::STATUS_CANCEL) {
            return '已取消';
        }
        $return = '未支付';
        if ($order['have_paid'] > 0) {
            $return = '制作中';
        }
        if ($order['have_made'] > 0) {
            if ($order['type'] == self::BUY_TYPE_TAKEIN) {
                $return = '待取餐';
            } else {
                $return = '配送中';
            }
        }
        if ($order['have_received'] > 0) {
            $return = '已完成';
        }
//        if ($order['have_commented'] > 0) {
//            $return = '已完成';
//        }
        return $return;
    }

    /**
     * 关联订单的商品
     */
    public function products()
    {
        return $this->hasMany('orderProduct', 'order_id', 'id');
    }

    /**
     * 关联扩展订单信息
     * @return \think\model\relation\HasOne
     */
    public function extend()
    {
        return $this->hasOne('orderExtend', 'order_id', 'id');
    }

    /**
     * 关联评价
     * @return \think\model\relation\HasOne
     */
    public function evaluate()
    {
        return $this->hasMany('evaluate', 'order_id', 'id');
    }

    /**
     * 店铺
     * @return \think\model\relation\HasOne
     */
    public function shop()
    {
        return $this->hasOne('shop', 'id', 'shop_id');
    }

    /**
     * 用户
     */
    public function user()
    {
        return $this->hasOne('user', 'id', 'user_id');
    }
}
