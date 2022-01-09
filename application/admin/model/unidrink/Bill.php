<?php

namespace app\admin\model\unidrink;

use think\Model;
use traits\model\SoftDelete;

class Bill extends Model
{

    use SoftDelete;



    // 表名
    protected $name = 'unidrink_bill';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text',
        'pay_type_text',
        'effecttime_text'
    ];



    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2'), '3' => __('Type 3')];
    }

    public function getPayTypeList()
    {
        return ['0' => __('Pay_type 0'), '5' => '余额支付', '3' => '微信支付', '4' => '支付宝'];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getEffecttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['effecttime']) ? $data['effecttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setEffecttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
