<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/5/29
 * Time: 10:28 AM
 */


namespace addons\unidrink\controller;


use addons\unidrink\model\Bill;
use addons\unidrink\model\Recharge;
use think\Exception;

/**
 * 我的钱包
 * Class Balance
 * @package addons\unidrink\controller
 */
class Balance extends Base
{
    /**
     * 允许频繁访问的接口（方法格式：小写）
     * @var array
     */
    protected $frequently = ['getBillList'];

    /**
     * @ApiTitle    (充值列表)
     * @ApiSummary  (充值列表)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="integer", description="充值项id")
     * @ApiReturnParams  (name="name", type="string", description="充值项名称")
     * @ApiReturnParams  (name="value", type="string", description="充值项价值")
     * @ApiReturnParams  (name="sell_price", type="string", description="充值项售价")
     *
     */
    public function getMoneyList()
    {
        try {

            $recharge = new Recharge();
            $result = $recharge->where(['status' => Recharge::STATUS_ON])->select();

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('', $result);
    }


    /**
     * @ApiTitle    (充值)
     * @ApiSummary  (充值)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="recharge_id", type=integer, required=true, description="充值项id")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="string", description="订单id")
     * @ApiReturnParams  (name="out_trade_no", type="string", description="商户订单号")
     *
     */
    public function recharge()
    {
        try {
            $rechargeId = $this->request->post('recharge_id');
            $recharge = new Recharge();
            $recharge = $recharge->where(['id' => $rechargeId, 'status' => Recharge::STATUS_ON])->find();
            if (!$recharge) {
                throw new Exception('充值金额必须大于0元');
            }
            $bill = new Bill();
            $result = $bill->createBill(
                null,
                $this->auth->id,
                $recharge['id'],
                $recharge['sell_price'],
                $recharge['value'],
                Bill::TYPE_RECHARGE
            );
            if ($result === false) {
                throw new Exception('错误');
            }

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('', $result);
    }


    /**
     * @ApiTitle    (账单列表)
     * @ApiSummary  (获取账单列表)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="page", type=integer, required=false, description="页面")
     * @ApiParams   (name="pagesize", type=integer, required=false, description="页数")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="string", description="账单id")
     * @ApiReturnParams  (name="out_trade_no", type="string", description="商户订单号")
     * @ApiReturnParams  (name="total_price", type="string", description="实付价格")
     * @ApiReturnParams  (name="real_price", type="string", description="真实人民币")
     * @ApiReturnParams  (name="pay_type", type="integer", description="支付类型")
     * @ApiReturnParams  (name="effecttime", type="integer", description="生效时间戳")
     * @ApiReturnParams  (name="effecttime_text", type="integer", description="生效时间")
     * @ApiReturnParams  (name="createtime", type="integer", description="创建订单时间戳")
     * @ApiReturnParams  (name="createtime_text", type="integer", description="创建订单时间")
     *
     */
    public function getBillList()
    {
        $page = $this->request->post('page', 1);
        $pagesize = $this->request->post('pagesize', 15);
        $type = $this->request->post('type', Bill::TYPE_ALL);

        $query = (new Bill)
            ->where(['user_id' => $this->auth->id]);

        if (in_array($type, [Bill::TYPE_CONSUME, Bill::TYPE_RECHARGE, Bill::TYPE_REFUND])) {
            $query->where('type = :type', ['type' => $type]);
        }

        $list = $query->where('effecttime', '>', 0)
            ->order('createtime DESC')
            ->page($page, $pagesize)
            ->select();

        if ($list) {
            $list = collection($list)->append(['createtime_text', 'effecttime_text'])->toArray();
        }
        $this->success('', $list);
    }


}
