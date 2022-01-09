<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/5/27
 * Time: 2:56 PM
 */


namespace addons\unidrink\model;


use addons\unidrink\extend\Hashids;
use addons\unidrink\extend\Snowflake;
use think\Model;
use traits\model\SoftDelete;

/**
 * 账单模型
 * Class Bill
 * @package addons\unidrink\model
 */
class Bill extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'unidrink_bill';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 账单类型
    const TYPE_ALL = 0;
    const TYPE_CONSUME = 1; // 消费
    const TYPE_RECHARGE = 2;// 充值
    const TYPE_REFUND = 3;  // 退款

    // 是否生效
    const NOT_EFFECTIVE = 0; // 未生效

    // 支付方式
    const PAY_NONE = 0; // 无
    const PAY_WXPAY = 3; // 微信支付
    const PAY_BALANCE = 5; // 余额支付


    public function getCreatetimeTextAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['createtime']);
    }

    public function getEffecttimeTextAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['effecttime']);
    }

    /**
     * 创建账单
     * @param int $userId 用户id
     * @param string $typeId 订单id
     * @param float $total_price 订单金额
     * @param float $realprice 实际金额
     * @param int $type 账单类型
     * @param int $payType 支付类型
     * @param int $status 是否生效
     * @return bool|int
     * @throws \Exception
     */
    public function createBill($out_trade_no = null, $userId, $typeId, $total_price = 0.00, $real_price = 0.00,$type = self::TYPE_CONSUME, $payType = self::PAY_NONE, $effecttime = SELF::NOT_EFFECTIVE)
    {
        if (is_null($out_trade_no)) {
            $out_trade_no = date('Ymd',time()).uniqid().$userId;
        }

        // 获取雪花算法分布式id，方便以后扩展
        $snowflake = new Snowflake();
        $id = $snowflake->id();

        $result = $this->save([
            'id' => $id,
            'user_id' => $userId,
            'out_trade_no' => $out_trade_no,
            'type' => $type,
            'total_price' => $total_price,
            'real_price' => $real_price,
            'type_id' => $typeId,
            'effecttime' => $effecttime,
            'pay_type' => $payType
        ]);

        if ($result) {
            return [
                'out_trade_no' => $out_trade_no,
                'id' => Hashids::encodeHex($id)
            ];
        }
        return false;
    }
}
