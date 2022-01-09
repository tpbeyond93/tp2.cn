<?php


namespace addons\unidrink\model;


use think\Model;
use traits\model\SoftDelete;

class Recharge extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'unidrink_recharge';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';


    // 是否显示
    const STATUS_ON = 1; // 是
    const STATUS_OFF = 0; // 否



}
