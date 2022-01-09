<?php

namespace app\admin\model\unidrink;

use think\Model;

class OrderExtend extends Model
{

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'unidrink_order_extend';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

}
