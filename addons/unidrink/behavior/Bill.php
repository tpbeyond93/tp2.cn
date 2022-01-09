<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/5/29
 * Time: 4:00 PM
 */


namespace addons\unidrink\behavior;

use addons\unidrink\model\User;
use app\common\model\MoneyLog;

/**
 * 账单行为
 * Class Bill
 * @package addons\unidrink\behavior
 */
class Bill
{
    /**
     * 支付成功 - 指充值
     * 行为一：更改订单的支付状态，更新支付时间。
     * 行为二：更新用户钱包，记录钱包记录
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
        /**
         * @var \addons\unidrink\model\Bill $bill
         */
        $bill = &$params;
        $bill->pay_type = $extra['pay_type'];
        $bill->effecttime = time();
        $bill->save();

        // type         tinyint(1)                  not null comment '类型:1=消费,2=充值,3=退款',
        // pay_type     tinyint(1)     default 0    not null comment '付款方式:0=无,5=余额支付,3=微信支付,4=支付宝',
        // 行为二
        $user = (new User())->where(['id' => $bill->user_id])->find();
        $before = $user->money;
        if ($bill->type == 1 && $bill->pay_type == 5) {
            $user->setDec('money', $bill->real_price);
            $after = $before - $bill->real_price;
            $memo = '余额购买';
        }
        if ($bill->type == 2) {
            $user->setInc('money', $bill->real_price);
            $after = $before + $bill->real_price;
            $memo = '充值到余额';
        }
        // 追加用户金额记录
        isset($memo) && (new MoneyLog())->save([
            'user_id' => $bill->user_id,
            'money' => $bill->real_price,
            'before' => $before,
            'after' => $after,
            'memo' => $memo
        ]);

        // More ...
    }

    /**
     * 支付失败 - 指充值
     * @param $params
     */
    public function paidFail(&$params)
    {
        if (empty($params)) {
            return;
        }
        $bill = &$params;
        $bill->effecttime = \addons\unidrink\model\Bill::NOT_EFFECTIVE;
        $bill->save();

        // More ...
    }

}
