<?php

namespace addons\unidrink\model;


use addons\unidrink\extend\Hashids;
use think\Model;

class Ads extends Model
{
    // 表名
    protected $name = 'unidrink_ads';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 是否显示
    const SWITCH_ON = 1; // 显示
    const SWITCH_OFF = 0; // 不显示

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
     * 更改字段的值
     */
    public function getProductIdAttr($value) {
        return Hashids::encodeHex($value);
    }
}
