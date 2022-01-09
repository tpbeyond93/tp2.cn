<?php

namespace app\admin\model\unidrink;

use think\Model;
use traits\model\SoftDelete;

class Servicemore extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'unidrink_service_more';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text'
    ];
    

    
    public function getTypeList()
    {
        return ['link' => __('Type link'), 'richtext' => __('Type richtext')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
