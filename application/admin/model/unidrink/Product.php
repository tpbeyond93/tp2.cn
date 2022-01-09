<?php

namespace app\admin\model\unidrink;

use think\Model;
use traits\model\SoftDelete;

class Product extends Model
{

    use SoftDelete;

    // 表名
    protected $name = 'unidrink_product';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    const ALL_SHOP = 0; //全部店铺

    // 追加属性
    protected $append = [

    ];


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }


    /**
     * 关联分类
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('category');
    }

    public function getImageTextAttr($value, $data) {
        return \addons\unidrink\model\Config::getImagesFullUrl($data['image']);
    }
}
