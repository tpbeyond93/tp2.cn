<?php

namespace addons\unidrink\model;


use think\Model;

/**
 * 用户的优惠券
 * Class Favorite
 * @package addons\unidrink\model
 */
class CouponUser extends Model
{

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'unidrink_coupon_user';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 是否已使用？
    const STATUS_ON = 1; //是
    const STATUS_OFF = 0; //否

    // 追加属性
    protected $append = [
        'starttime_text',
        'endtime_text',
        'createtime_text'
    ];

    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['starttime']) ? $data['starttime'] : '');
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }


    public function getEndtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['endtime']) ? $data['endtime'] : '');
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }

    public function getCreatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['createtime']) ? $data['createtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i", $value) : $value;
    }

}
