<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/1/7
 * Time: 10:01 下午
 */


namespace addons\unidrink\controller;

use addons\unidrink\extend\Ali;
use addons\unidrink\extend\Hashids;
use addons\unidrink\extend\Wechat;
use addons\unidrink\model\Bill;
use addons\unidrink\model\Config;
use app\common\model\MoneyLog;
use think\Db;
use think\Exception;
use think\Hook;
use think\Log;

/**
 * 支付接口
 * Class Pay
 * @package addons\unidrink\controller
 */
class Pay extends Base
{
    protected $noNeedLogin = ['notify', 'authRedirect', 'alipay', 'alinotify'];

    /**
     * 根据out_trade_no商户订单号获取订单信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getOrder()
    {

        $out_trade_no = $this->request->request('out_trade_no', 0);
        $orderModel = new \addons\unidrink\model\Order();
        $order = $orderModel->where(['out_trade_no' => $out_trade_no])->find();
        $billModel = new Bill();
        $bill = $billModel->where(['out_trade_no' => $out_trade_no])->find();

        if (!$order && !$bill) {
            $this->error(__('Order does not exist'));
        }
        if ($order) {
            $products = $order->products()->select();
            $body = Config::getByName('name')['value'];
            foreach ($products as $product) {
                $body .= '_' . $product['title'];
            }
        }
        if ($bill) {
            $body = __('Recharge');
            $order = $bill;
        }
        return [$order, $body];
    }

    /**
     * @ApiTitle    (微信统一下单接口)
     * @ApiSummary  (微信统一下单接口)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="out_trade_no", type="string", required=true, description="商户订单号")
     * @ApiReturn   ({"code":1,"msg":"","data":{}})
     *
     * @ApiReturnParams  (name="return_code", type="string", description="状态码")
     * @ApiReturnParams  (name="result_code", type="string", description="状态码")
     * @ApiReturnParams  (name="return_msg", type="string", description="状态信息")
     * @ApiReturnParams  (name="appid", type="string", description="小程序app_id")
     * @ApiReturnParams  (name="mch_id", type="string", description="商户号")
     * @ApiReturnParams  (name="nonce_str", type="string", description="支付签名随机串")
     * @ApiReturnParams  (name="sign", type="string", description="签名")
     * @ApiReturnParams  (name="trade_type", type="string", description="支付类型")
     * @ApiReturnParams  (name="timeStamp", type="string", description="时间戳")
     * @ApiReturnParams  (name="paySign", type="string", description="支付签名")
     * @ApiReturnParams  (name="prepay_id", type="string", description="统一支付接口返回的prepay_id参数值，提交格式如：package: 'prepay_id=' + data.prepay_id")
     *
     */
    public function unify()
    {
        try {
            list ($order, $body) = $this->getOrder();

            $platfrom = $this->request->header('platform', 'MP-WEIXIN');

            $trade_type = Wechat::getTradeType($platfrom);

            $app = Wechat::initEasyWechat('payment');
            $result = $app->order->unify([
                'body' => $body,
                'out_trade_no' => $order['out_trade_no'],
                'total_fee' => bcmul($order['total_price'], 100),
                'spbill_create_ip' => $_SERVER['REMOTE_ADDR'], // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                'trade_type' => $trade_type, // 请对应换成你的支付方式对应的值类型
                'openid' => Wechat::getOpenidByUserId($this->auth->id)
            ]);

            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {

                if ($trade_type == 'JSAPI') {
                    // 二次签名
                    $result['timeStamp'] = (string)time();
                    $result['paySign'] = Wechat::paySign([
                        'appId' => Config::getByName('app_id')['value'],
                        'nonceStr' => $result['nonce_str'],
                        'package' => 'prepay_id=' . $result['prepay_id'],
                        'timeStamp' => $result['timeStamp'],
                        'signType' => 'MD5'
                    ], Config::getByName('key')['value']);
                } elseif ($trade_type == 'MWEB') {
                    $page = '/pages/take-foods/take-foods';
                    if ($platfrom == 'APP-PLUS') {
                        $page = '/pages/index/index';
                    }
                    $result['mweb_url'] .= '&redirect_url=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . '/h5/#' . $page);
                    $result['referer'] = 'https://' . $_SERVER['HTTP_HOST'];
                } elseif ($trade_type == 'APP') {

                    $result['orderInfo']['appid'] = $result['appid'];
                    $result['orderInfo']['noncestr'] = $result['nonce_str'];
                    $result['orderInfo']['package'] = "Sign=WXPay";
                    $result['orderInfo']['partnerid'] = $result['mch_id'];
                    $result['orderInfo']['prepayid'] = $result['prepay_id'];
                    $result['orderInfo']['timestamp'] = (string)time();

                    $result['orderInfo']['sign'] = Wechat::paySign(
                        $result['orderInfo'],
                        Config::getByName('key')['value']
                    );
                }

            } else {
                $this->error($result['err_code_des'] ?? $result['return_msg']);
            }

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('', $result);
    }

    /**
     * 微信订单支付通知回调
     * @ApiInternal
     */
    public function notify()
    {

        $app = Wechat::initEasyWechat('payment');
        $response = $app->handlePaidNotify(function ($message, $fail) use ($app) {
            try {
                // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
                // 普通订单
                $orderModel = new \addons\unidrink\model\Order(); //($message['out_trade_no']);
                $order = $orderModel->where(['out_trade_no' => $message['out_trade_no']])->find();

                // 充值订单
                $billModel = new Bill();
                $bill = $billModel->where(['out_trade_no' => $message['out_trade_no']])->find();

                if (!$order && !$bill) {
                    return true;  // 告诉微信，订单没找到，别再通知我了
                }
                if ($order) {
                    if ($order->have_paid != \addons\unidrink\model\Order::PAID_NO) {
                        return true; // 告诉微信，我已经处理完了，别再通知我了
                    }
                }
                if ($bill) {
                    if ($bill->effecttime != Bill::NOT_EFFECTIVE) {
                        return true; // 告诉微信，我已经处理完了，别再通知我了
                    }
                }

                // 这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付
                $result = $app->order->queryByOutTradeNumber($message['out_trade_no']);
                if ($result['return_code'] == 'FAIL' || empty($result['result_code']) || $result['result_code'] == 'FAIL') {
                    return $fail('订单未支付');
                }

                // 检查是否成功
                if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                    // 用户是否支付成功
                    if ($message['result_code'] === 'SUCCESS') {

                        Hook::exec('addons\\unidrink\\behavior\\Order', 'paid_success', $order, ['pay_type' => \addons\unidrink\model\Order::PAY_WXPAY]);
                        Hook::exec('addons\\unidrink\\behavior\\Bill', 'paid_success', $bill, ['pay_type' => Bill::PAY_WXPAY]);

                    } elseif ($message['result_code'] === 'FAIL') {
                        // 用户支付失败
                        Hook::exec('addons\\unidrink\\behavior\\Order', 'paid_fail', $order);
                        Hook::exec('addons\\unidrink\\behavior\\Bill', 'paid_fail', $bill);
                    }
                } else {
                    throw new \Exception(json_encode($message));
                }

                return true;
            } catch (\Exception $e) {
                // 记录日志
                Log::record('支付回调错误：' . $e->getMessage());

                return $fail('通信失败，请稍后再通知我');
            }
        });

        $response->send();
    }

    /**
     * 微信内H5-JSAPI支付
     * @ApiInternal
     */
    public function jssdkBuildConfig()
    {
        $app = Wechat::initEasyWechat('payment');
        $configData = $app->jssdk->buildConfig(['chooseWXPay'], true, true, false);
        Log::record('jssdkBuildConfig');
        Log::record(json_encode($configData));
        $this->success('', $configData);
    }


    /**
     * @ApiTitle    (余额支付)
     * @ApiSummary  (余额支付)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="out_trade_no", type="string", required=true, description="商户订单号")
     * @ApiReturn   ({"code":1,"msg":"","data":1})
     *
     */
    public function balance()
    {
        try {
            Db::startTrans();
            $out_trade_no = $this->request->request('out_trade_no', 0);
            $orderModel = new \addons\unidrink\model\Order();
            $order = $orderModel->where(['out_trade_no' => $out_trade_no, 'user_id' => $this->auth->id])->find();
            $billModel = new Bill();
            $bill = $billModel->where(['out_trade_no' => $out_trade_no, 'user_id' => $this->auth->id])->find();

            if (empty($order) || empty($bill)) {
                $this->error('订单不存在');
            }
            $user = (new \addons\unidrink\model\User)->where(['id' => $this->auth->id])->find();
            if ($user->money < $order->total_price) {
                throw new Exception('余额不足,请充值');
            }
            if ((isset($order) && $order->have_paid > 0) || (isset($bill) && $bill->effecttime > 0)) {
                throw new Exception('订单已支付，无需再支付');
            }

            // 更新订单和账单的状态
            Hook::exec('addons\\unidrink\\behavior\\Order', 'paid_success', $order, ['pay_type' => \addons\unidrink\model\Order::PAY_BALANCE]);
            Hook::exec('addons\\unidrink\\behavior\\Bill', 'paid_success', $bill, ['pay_type' => Bill::PAY_BALANCE]);

            Db::commit();

        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('', 1);
    }


    /**
     * @ApiTitle    (支付宝支付)
     * @ApiSummary  (支付宝支付)
     * @ApiMethod   (GET)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="order_id", type="string",required=true, description="订单id")
     * @ApiReturn   (重定向到支付宝支付网页)
     *
     */
    public function alipay()
    {
        $out_trade_no = $this->request->post('out_trade_no', 0);

        $orderModel = new \addons\unidrink\model\Order();
        $order = $orderModel->where(['out_trade_no' => $out_trade_no])->find();

        try {
            if (!$order) {
                $this->error(__('Order does not exist'));
            }
            $products = $order->products()->select();

            $body = Config::getByName('name')['value'];
            foreach ($products as $product) {
                $body .= '_' . $product['title'];
            }

            $platfrom = $this->request->header('platform', 'H5');
            $alipay = Ali::initAliPay();
            $order = [
                'out_trade_no' => $order->out_trade_no,
                'total_amount' => $order->total_price,
                'subject' => $body,
                'http_method' => 'GET' // 如果想在 wap 支付时使用 GET 方式提交，请加上此参数。默认使用 POST 方式提交
            ];

            switch ($platfrom) {
                case 'H5':
                    // 直接返回
                    $alipay->wap($order)->send();
                    break;
                case 'APP-PLUS':
                    //$pay->app($order)->send();
                    $this->success('', $alipay->app($order)->getContent());
                    break;
                case 'MP-ALIPAY':
                    // 支付宝小程序

                    break;
                default:
                    $this->error('此平台不支持支付宝支付');
            }

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

    }

    /**
     * 支付宝回调地址
     * @ApiInternal
     */
    public function alinotify()
    {
        $alipay = Ali::initAliPay();

        try {
            $data = $alipay->verify(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
            if (in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                // 支付成功
                //Log::record('Alipay notify ,支付成功');

                // 条件一
                $orderModel = new \addons\unidrink\model\Order(); //($message['out_trade_no']);
                $order = $orderModel->where(['out_trade_no' => $data['out_trade_no']])->find();
                if (!$order || $order->have_paid != \addons\unidrink\model\Order::PAID_NO) {
                    throw new Exception('订单不存在或已完成');
                }

                // 条件二
                if ($order->total_price > $data['total_amount'] || $order->total_price < $data['total_amount']) {
                    throw new Exception('金额不一');
                }

                // 条件三
                if ($data['app_id'] != Config::getByName('ali_app_id')['value']) {
                    throw new Exception('app_id不一');
                }

                // 添加行为
                Hook::add('paid_success', 'addons\\unidrink\\behavior\\Order');
                Hook::listen('paid_success', $order, ['pay_type' => \addons\unidrink\model\Order::PAY_ALIPAY]);

            }

        } catch (\Exception $e) {
            Log::record('Alipay notify ,支付失败: ' . $e->getMessage());
            return $alipay->success()->send();
        }

        return $alipay->success()->send();// laravel 框架中请直接 `return $alipay->success()`
    }
}
