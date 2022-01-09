<?php

namespace app\admin\model\unidrink;

use think\Model;

/**
 * 收入统计view
 */
class Income extends Model
{

    // 表名
    protected $name = 'unidrink_income';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

}
