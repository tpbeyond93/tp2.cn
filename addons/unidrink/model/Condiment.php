<?php

namespace addons\unidrink\model;


use think\Model;
use traits\model\SoftDelete;

/**
 * 小料模型
 * Class Favorite
 * @package addons\unidrink\model
 */
class Condiment extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'unidrink_condiment';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 是否上架
    const SWITCH_ON = 1; // 上架
    const SWITCH_OFF = 0; // 下架

}
