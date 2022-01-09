<?php

namespace app\admin\model\unidrink;

use think\Model;
use traits\model\SoftDelete;

class Service extends Model
{

    use SoftDelete;



    // 表名
    protected $name = 'unidrink_service';

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

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $row->save(['weigh' => $row['id']]);
        });
    }

    public function getTypeList()
    {
        return [
            'pages' => __('Type pages'),
            'miniprogram' => __('Type miniprogram'),
            'menu' => __('Type menu'),
            'content' => __('Content')
        ];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    /**
     * 关联上一级
     * @return \think\model\relation\HasOne
     */
    public function parent()
    {
        return $this->hasOne('service', 'id', 'pid');
    }

}
