<?php

namespace app\admin\model\unidrink;

use think\Model;
use traits\model\SoftDelete;

class Order extends Model
{

    use SoftDelete;



    // 表名
    protected $name = 'unidrink_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'pay_type_text',
        'type_text',
        'status_text',
        'createtime_text',
        'time_consuming',
        'gettime_text'
    ];

    /**
     * 计算耗时时间
     */
    public function getTimeConsumingAttr($value, $data)
    {
        $time = time() - $data['createtime'];
        return $time.'秒';
    }

    public function getCreatetimeTextAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['createtime']);
    }

    public function getPayTypeList()
    {
        return ['5' => '余额支付', '3' => '微信支付'];
    }

    public function getTypeList()
    {
        return ['1' => '自取', '2' => '外卖'];
    }

    public function getStatusList()
    {
        return ['-1' => '退款', '0' => '取消订单', '1' => '正常'];
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    protected function setHavePaidAttr($value)
    {
        return $value === '' ? 0 : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setHaveDeliveredAttr($value)
    {
        return $value === '' ? 0 : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setHaveCommentedAttr($value)
    {
        return $value === '' ? 0 : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setHaveReceivedAttr($value)
    {
        return $value === '' ? 0 : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function getGettimeTextAttr($value, $data)
    {
        return date('H:i', $data['gettime']);
    }

    public function shop()
    {
        return $this->belongsTo('shop', 'shop_id', 'id');
    }

    /**
     * 关联订单商品
     */
    public function product(){
        return $this->hasMany('orderProduct', 'order_id', 'id');
    }

    /**
     * 关联订单扩展
     */
    public function extend() {
        return $this->hasOne('orderExtend', 'order_id', 'id');
    }

}
