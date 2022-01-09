<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/1/6
 * Time: 11:25 下午
 */


namespace addons\unidrink\model;

use think\Model;

/**
 * 订单商品表
 * Class OrderExtend
 * @package addons\unidrink\model
 */
class OrderProduct extends Model
{
    // 表名
    protected $name = 'unidrink_order_product';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    /**
     * 关联商品信息
     */
    public function product()
    {
        return $this->hasOne('product', 'id', 'product_id');
    }
}
