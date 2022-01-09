<?php

namespace addons\unidrink\controller;

use addons\unidrink\extend\Hashids;
use addons\unidrink\model\Config;
use addons\unidrink\model\Evaluate;
use addons\unidrink\model\OrderRefund;
use app\admin\model\unidrink\OrderRefundProduct;
use think\Db;
use think\Exception;
use think\Hook;
use think\Loader;

/**
 * 订单接口
 * Class Order
 * @package addons\unidrink\controller
 */
class Order extends Base
{

    /**
     * 允许频繁访问的接口
     * @var array
     */
    protected $frequently = ['getorders'];

    protected $noNeedLogin = ['count'];

    /**
     * @ApiTitle    (创建订单)
     * @ApiSummary  (创建订单)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="type", type=integer, required=true, description="购买类型:1=自取,2=外卖")
     * @ApiParams   (name="address_id", type=integer, required=true, description="外卖配送地址id")
     * @ApiParams   (name="shop_id", type=integer, required=true, description="商店id")
     * @ApiParams   (name="mobile", type=string, required=true, description="联系电话")
     * @ApiParams   (name="gettime", type=string, required=true, description="取餐时间")
     * @ApiParams   (name="pay_type", type=string, required=true, description="支付类型")
     * @ApiParams   (name="remark", type=string, required=true, description="备注")
     * @ApiParams   (name="product_id", type=array, required=true, description="商品id数组")
     * @ApiParams   (name="spec", type=array, required=true, description="商品规格数组")
     * @ApiParams   (name="number", type=array, required=true, description="商品数量数组")
     * @ApiParams   (name="coupon_id", type=integer, required=true, description="优惠券id")
     * @ApiReturn   ({"code":1,"msg":"","data":{}})
     *
     * @ApiReturnParams  (name="order_id", type="string", description="订单编号")
     * @ApiReturnParams  (name="out_trade_no", type="string", description="商户订单号（支付用）")
     *
     */
    public function submit()
    {
        $data = $this->request->post();
        try {
            $validate = Loader::validate('\\addons\\unidrink\\validate\\Order');
            if (!$validate->check($data, [], 'submit')) {
                throw new Exception($validate->getError());
            }

            Db::startTrans();

            // 判断创建订单的条件
            if (empty(Hook::get('create_order_before'))) {
                Hook::add('create_order_before', 'addons\\unidrink\\behavior\\Order');
            }
            // 减少商品库存，增加"已下单未支付数量"
            if (empty(Hook::get('create_order_after'))) {
                Hook::add('create_order_after', 'addons\\unidrink\\behavior\\Order');
            }

            $orderModel = new \addons\unidrink\model\Order();
            $result = $orderModel->createOrder($this->auth->id, $data);

            Db::commit();


        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage(), false);
        }
        $this->success('', $result);
    }

    /**
     * @ApiTitle    (获取订单列表)
     * @ApiSummary  (获取订单列表)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     *
     * @ApiParams   (name="page", type=integer, required=true, description="页面")
     * @ApiParams   (name="pagesize", type=integer, required=true, description="页数")
     *
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="shop_id", type="integer", description="店铺id")
     * @ApiReturnParams  (name="out_trade_no", type="string", description="商户订单号")
     * @ApiReturnParams  (name="order_price", type="string", description="订单金额")
     * @ApiReturnParams  (name="discount_price", type="string", description="优惠金额")
     * @ApiReturnParams  (name="delivery_price", type="string", description="配送费")
     * @ApiReturnParams  (name="total_price", type="string", description="实付金额")
     * @ApiReturnParams  (name="pay_type", type="inte", description="支付类型")
     * @ApiReturnParams  (name="type", type="integer", description="购买类型:1=自取,2=外卖")
     * @ApiReturnParams  (name="ip", type="string", description="下单ip")
     * @ApiReturnParams  (name="remark", type="string", description="备注")
     * @ApiReturnParams  (name="status", type="integer", description="订单状态:-1=退款,0=取消订单,1=正常啊")
     * @ApiReturnParams  (name="have_paid", type="integer", description="支付时间")
     * @ApiReturnParams  (name="have_made", type="integer", description="出单时间")
     * @ApiReturnParams  (name="have_received", type="integer", description="收货时间")
     * @ApiReturnParams  (name="have_received", type="integer", description="收货时间")
     * @ApiReturnParams  (name="have_commented", type="integer", description="评价时间")
     * @ApiReturnParams  (name="number_id", type="integer", description="取餐号")
     * @ApiReturnParams  (name="gettime", type="integer", description="取餐时间戳")
     * @ApiReturnParams  (name="gettime_text", type="string", description="取餐时间")
     * @ApiReturnParams  (name="order_id", type="string", description="订单id")
     * @ApiReturnParams  (name="order_status_text", type="string", description="订单状态制")
     *
     * @ApiReturnParams  (name="products", type="array", description="商品数组")
     * @ApiReturnParams  (name="products.id", type="integer", description="订单的商品id")
     * @ApiReturnParams  (name="products.title", type="string", description="商品标题")
     * @ApiReturnParams  (name="products.image", type="string", description="商品图片")
     * @ApiReturnParams  (name="products.number", type="integer", description="商品数量")
     * @ApiReturnParams  (name="products.price", type="string", description="商品价格")
     * @ApiReturnParams  (name="products.spec", type="string", description="商品规格")
     * @ApiReturnParams  (name="products.product_id", type="integer", description="基础商品id")
     * @ApiReturnParams  (name="products.evaluate", type="integer", description="是否已评价")
     *
     * @ApiReturnParams  (name="shop.id", type="integer", description="店铺id")
     * @ApiReturnParams  (name="shop.name", type="string", description="店铺名字")
     *
     */
    public function getOrders()
    {
        $page = $this->request->post('page', 1);
        $pagesize = $this->request->post('pagesize', 10);
        try {

            $orderModel = new \addons\unidrink\model\Order();
            $result = $orderModel->getOrdersByType($this->auth->id, $page, $pagesize);
            $this->success('', $result);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }


    /**
     * 发表评论
     * @ApiInternal
     */
    public function comment()
    {
        $rate = $this->request->post('rate', 5);
        $anonymous = $this->request->post('anonymous', 0);
        $comment = $this->request->post('comment');
        $order_id = $this->request->post('order_id', 0);
        $order_id = \addons\unidrink\extend\Hashids::decodeHex($order_id);
        $product_id = $this->request->post('product_id');
        $product_id = \addons\unidrink\extend\Hashids::decodeHex($product_id);

        $orderProductModel = new \addons\unidrink\model\OrderProduct();
        $orderProduct = $orderProductModel->where(['product_id' => $product_id, 'order_id' => $order_id, 'user_id' => $this->auth->id])->find();

        $orderModel = new \addons\unidrink\model\Order();
        $order = $orderModel->where(['id' => $order_id, 'user_id' => $this->auth->id])->find();

        if (!$orderProduct || !$order) {
            $this->error(__('Order not exist'));
        }
        if ($order->have_received == $orderModel::RECEIVED_NO) {
            $this->error(__('未收货，不可评价'));
        }

        $result = false;
        try {

            $evaluate = new Evaluate();
            $evaluate->user_id = $this->auth->id;
            $evaluate->order_id = $order_id;
            $evaluate->product_id = $product_id;
            $evaluate->rate = $rate;
            $evaluate->anonymous = $anonymous;
            $evaluate->comment = $comment;
            $evaluate->spec = $orderProduct->spec;
            $result = $evaluate->save();

            if ($result) {
                $order->have_commented = time();
                $order->save();
            }

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        if ($result !== false) {
            $this->success(__('Thanks for the evaluation'));
        } else {
            $this->error(__('Evaluation failure'));
        }

    }

    /**
     * @ApiTitle    (订单详情)
     * @ApiSummary  (订单详情)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     *
     * @ApiParams   (name="id", type=integer, required=true, description="订单id")
     *
     * @ApiReturn   ({"code":1,"msg":"","data":{}})
     *
     * @ApiReturnParams  (name="shop_id", type="integer", description="店铺id")
     * @ApiReturnParams  (name="out_trade_no", type="string", description="商户订单号")
     * @ApiReturnParams  (name="order_price", type="string", description="订单金额")
     * @ApiReturnParams  (name="discount_price", type="string", description="优惠金额")
     * @ApiReturnParams  (name="delivery_price", type="string", description="配送费")
     * @ApiReturnParams  (name="total_price", type="string", description="实付金额")
     * @ApiReturnParams  (name="pay_type", type="inte", description="支付类型")
     * @ApiReturnParams  (name="type", type="integer", description="购买类型:1=自取,2=外卖")
     * @ApiReturnParams  (name="type_text", type="integer", description="购买类型")
     * @ApiReturnParams  (name="ip", type="string", description="下单ip")
     * @ApiReturnParams  (name="remark", type="string", description="备注")
     * @ApiReturnParams  (name="status", type="integer", description="订单状态:-1=退款,0=取消订单,1=正常啊")
     * @ApiReturnParams  (name="have_paid", type="integer", description="支付时间")
     * @ApiReturnParams  (name="have_made", type="integer", description="出单时间")
     * @ApiReturnParams  (name="have_received", type="integer", description="收货时间")
     * @ApiReturnParams  (name="have_received", type="integer", description="收货时间")
     * @ApiReturnParams  (name="have_commented", type="integer", description="评价时间")
     * @ApiReturnParams  (name="number_id", type="integer", description="取餐号")
     * @ApiReturnParams  (name="gettime", type="integer", description="取餐时间戳")
     * @ApiReturnParams  (name="gettime_text", type="string", description="取餐时间")
     * @ApiReturnParams  (name="order_id", type="string", description="订单id")
     * @ApiReturnParams  (name="order_status_text", type="string", description="订单状态制")
     *
     * @ApiReturnParams  (name="products", type="array", description="商品数组")
     * @ApiReturnParams  (name="products.id", type="integer", description="订单的商品id")
     * @ApiReturnParams  (name="products.title", type="string", description="商品标题")
     * @ApiReturnParams  (name="products.image", type="string", description="商品图片")
     * @ApiReturnParams  (name="products.number", type="integer", description="商品数量")
     * @ApiReturnParams  (name="products.price", type="string", description="商品价格")
     * @ApiReturnParams  (name="products.spec", type="string", description="商品规格")
     * @ApiReturnParams  (name="products.product_id", type="integer", description="基础商品id")
     * @ApiReturnParams  (name="products.evaluate", type="integer", description="是否已评价")
     *
     * @ApiReturnParams  (name="shop.id", type="integer", description="店铺id")
     * @ApiReturnParams  (name="shop.name", type="string", description="店铺名字")
     *
     * @ApiReturnParams  (name="address.id", type="integer", description="收货地址id")
     * @ApiReturnParams  (name="address.user_id", type="integer", description="用户id")
     * @ApiReturnParams  (name="address.name", type="string", description="收货用户名称")
     * @ApiReturnParams  (name="address.mobile", type="string", description="收货用户电话")
     * @ApiReturnParams  (name="address.address", type="string", description="收货用户地址")
     * @ApiReturnParams  (name="address.lng", type="string", description="收货经度")
     * @ApiReturnParams  (name="address.lat", type="string", description="收货纬度")
     * @ApiReturnParams  (name="address.door_number", type="string", description="门牌号")
     * @ApiReturnParams  (name="address.sex", type="integer", description="性别")
     *
     */
    public function detail()
    {
        $order_id = $this->request->request('id', 0);
        $order_id = \addons\unidrink\extend\Hashids::decodeHex($order_id);

        try {
            $orderModel = new \addons\unidrink\model\Order();
            $order = $orderModel
                ->with([
                    'products' => function ($query) {
                        $query->field('id,order_id,image,number,price,spec,title,product_id');
                    },
                    'extend' => function ($query) {
                        $query->field('id,order_id,address_id,address_json');
                    },
                    'evaluate' => function ($query) {
                        $query->field('id,order_id,product_id');
                    },
                    'shop' => function ($query) {
                        $query->field('id,name,mobile,lng,lat,address_map,address');
                    }
                ])
                ->where(['id' => $order_id, 'user_id' => $this->auth->id])->find();

            if ($order) {
                $order = $order->append(['paidtime', 'receivedtime', 'commentedtime', 'pay_type_text', 'type_text'])->toArray();

                // 送货地址
                if ($order['type'] == \addons\unidrink\model\Order::BUY_TYPE_TAKEOUT) {
                    $address = json_decode($order['extend']['address_json'], true);
                    $order['address'] = $address;
                } else {
                    $order['address'] = [];
                }

                // 是否已评论
                $evaluate = array_column($order['evaluate'], 'product_id');
                foreach ($order['products'] as &$product) {
                    $product['image'] = Config::getImagesFullUrl($product['image']);
                    if (in_array($product['id'], $evaluate)) {
                        $product['evaluate'] = true;
                    } else {
                        $product['evaluate'] = false;
                    }
                }

                // 订单状态
                $order['order_status_text'] = $orderModel->orderStatusText($order);

                unset($order['evaluate']);
                unset($order['extend']);
            }

            $this->success('', $order);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

    }

    /**
     * @ApiTitle    (取餐列表)
     * @ApiSummary  (取餐列表)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     *
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="shop_id", type="integer", description="店铺id")
     * @ApiReturnParams  (name="out_trade_no", type="string", description="商户订单号")
     * @ApiReturnParams  (name="order_price", type="string", description="订单金额")
     * @ApiReturnParams  (name="discount_price", type="string", description="优惠金额")
     * @ApiReturnParams  (name="delivery_price", type="string", description="配送费")
     * @ApiReturnParams  (name="total_price", type="string", description="实付金额")
     * @ApiReturnParams  (name="pay_type", type="inte", description="支付类型")
     * @ApiReturnParams  (name="type", type="integer", description="购买类型:1=自取,2=外卖")
     * @ApiReturnParams  (name="ip", type="string", description="下单ip")
     * @ApiReturnParams  (name="remark", type="string", description="备注")
     * @ApiReturnParams  (name="status", type="integer", description="订单状态:-1=退款,0=取消订单,1=正常啊")
     * @ApiReturnParams  (name="have_paid", type="integer", description="支付时间")
     * @ApiReturnParams  (name="have_made", type="integer", description="出单时间")
     * @ApiReturnParams  (name="have_received", type="integer", description="收货时间")
     * @ApiReturnParams  (name="have_received", type="integer", description="收货时间")
     * @ApiReturnParams  (name="have_commented", type="integer", description="评价时间")
     * @ApiReturnParams  (name="number_id", type="integer", description="取餐号")
     * @ApiReturnParams  (name="gettime", type="integer", description="取餐时间戳")
     * @ApiReturnParams  (name="gettime_text", type="string", description="取餐时间")
     * @ApiReturnParams  (name="order_id", type="string", description="订单id")
     * @ApiReturnParams  (name="order_status_text", type="string", description="订单状态制")
     *
     * @ApiReturnParams  (name="products", type="array", description="商品数组")
     * @ApiReturnParams  (name="products.id", type="integer", description="订单的商品id")
     * @ApiReturnParams  (name="products.title", type="string", description="商品标题")
     * @ApiReturnParams  (name="products.image", type="string", description="商品图片")
     * @ApiReturnParams  (name="products.number", type="integer", description="商品数量")
     * @ApiReturnParams  (name="products.price", type="string", description="商品价格")
     * @ApiReturnParams  (name="products.spec", type="string", description="商品规格")
     * @ApiReturnParams  (name="products.product_id", type="integer", description="基础商品id")
     * @ApiReturnParams  (name="products.evaluate", type="integer", description="是否已评价")
     *
     * @ApiReturnParams  (name="shop.id", type="integer", description="店铺id")
     * @ApiReturnParams  (name="shop.name", type="string", description="店铺名字")
     *
     */
    public function takeFoods()
    {
        $prefix = \think\Config::get('database.prefix');

        $orderModel = new \addons\unidrink\model\Order();
        $list = $orderModel
            ->alias('o')
            ->with(['shop', 'products'])
            ->where(['user_id' => $this->auth->id])
            ->where('status = 1 and have_paid > 0 and have_received = 0')
            ->field('o.*,(select COUNT(*) from ' . $prefix . 'unidrink_order WHERE shop_id = o.shop_id && have_paid > 0 and have_made = 0 && status = 1 && createtime < o.createtime) as prev_num')
            ->order('have_paid ASC')
            ->select();

        if ($list) {
            $list = collection($list)->append(['madetime', 'pay_type_text', 'type_text'])->toArray();
        }

        $this->success('', [
            'list' => $list,
            'concurrent' => Config::getByName('concurrent')['value'] ?? 5
        ]);

    }


    /**
     * @ApiTitle    (确认收到)
     * @ApiSummary  (确认收到)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     *
     * @ApiParams   (name="id", type=integer, required=true, description="订单id")
     *
     * @ApiReturn   ({"code":1,"msg":"","data":1})
     *
     */
    public function receive()
    {
        $order_id = $this->request->request('id');
        $order_id = \addons\unidrink\extend\Hashids::decodeHex($order_id);

        try {
            $order = \addons\unidrink\model\Order::update(
                [
                    'have_received' => time()
                ],
                [
                    'id' => $order_id,
                    'user_id' => $this->auth->id
                ]);
            if ($order) {
                $this->success('成功', time());
            } else {
                $this->error('失败');
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

    }
}
