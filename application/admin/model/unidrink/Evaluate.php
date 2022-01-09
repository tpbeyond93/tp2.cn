<?php

namespace app\admin\model\unidrink;

use think\Model;
use traits\model\SoftDelete;

class Evaluate extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'unidrink_evaluate';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'rate_text',
        'anonymous_text',
        'toptime_text'
    ];
    

    
    public function getRateList()
    {
        return ['1' => __('Rate 1'), '2' => __('Rate 2'), '3' => __('Rate 3'), '4' => __('Rate 4'), '5' => __('Rate 5')];
    }

    public function getAnonymousList()
    {
        return ['0' => __('Anonymous 0'), '1' => __('Anonymous 1')];
    }


    public function getRateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['rate']) ? $data['rate'] : '');
        $list = $this->getRateList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAnonymousTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['anonymous']) ? $data['anonymous'] : '');
        $list = $this->getAnonymousList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getToptimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['toptime']) ? $data['toptime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setToptimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
