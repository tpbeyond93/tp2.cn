<?php

namespace addons\unidrink\model;


use think\Model;

/**
 * 订单取餐号
 * Class Favorite
 * @package addons\unidrink\model
 */
class OrderNumber extends Model
{

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'unidrink_order_number';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;


}
