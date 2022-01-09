<?php

namespace addons\unidrink\model;


use think\Model;

/**
 * 收货地址模型
 * Class Favorite
 * @package addons\unidrink\model
 */
class Area extends Model
{
    // 表名
    protected $name = 'unidrink_area';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


}
