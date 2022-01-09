<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/3/16
 * Time: 5:03 PM
 */


namespace addons\unidrink\model;


use think\Model;

class Category extends Model
{
    // 表名
    protected $name = 'unidrink_category';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 代表全部店铺
    const ALL_SHOP = 0;

    /**
     * 处理图片
     * @param $value
     * @return string
     */
    public function getImageAttr($value) {
        return Config::getImagesFullUrl($value);
    }

    /**
     * 分类下的商品
     */
    public function goodsList()
    {
        return $this->hasMany('product', 'category_id', 'id');
    }
}
