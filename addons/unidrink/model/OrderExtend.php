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
 * 订单扩展模型
 * Class OrderExtend
 * @package addons\unidrink\model
 */
class OrderExtend extends Model
{
    // 表名
    protected $name = 'unidrink_order_extend';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 隐藏属性
    protected $hidden = [
        'order_id',
        'product_id'
    ];

    /**
     * 关联地址信息
     */
    public function address()
    {
        $this->belongsTo('address', 'address_id', 'id');
    }
}
