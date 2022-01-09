<?php

namespace addons\unidrink\model;


use addons\unidrink\extend\Hashids;
use think\Model;
use traits\model\SoftDelete;

class Evaluate extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'unidrink_evaluate';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    /**
     * 加密商品id
     * @param $value
     * @return string
     */
    public function getProductIdAttr($value)
    {
        return Hashids::encodeHex($value);
    }

    // 是否置顶
    const TOP_OFF = 0;

    // 是否匿名
    const ANONYMOUS_YES = 1; // 是
    const ANONYMOUS_NO = 0; // 否

    public function getCreatetimeTextAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['createtime']);
    }

    public function getUsernameAttr($value, $data) {
        if ($data['anonymous'] == self::ANONYMOUS_YES) {
            return __('Anonymous');
        } else {
            return $data['username'] ? $data['username'] : __('Tourist');
        }
    }

    public function getAvatarAttr($value, $data){
        if ($data['anonymous'] == self::ANONYMOUS_YES) {
            $data['avatar'] = Config::getByName('avatar')['value'];
        } else {
            $data['avatar'] = $data['avatar'] ? $data['avatar'] : Config::getByName('avatar')['value'];
        }
        return Config::getImagesFullUrl($data['avatar']);
    }
}
