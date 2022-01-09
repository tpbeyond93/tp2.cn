<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/3/8
 * Time: 7:25 PM
 */


namespace addons\unidrink\model;


use think\Model;

/**
 * 基础用户表
 * @property UserExtend $extend
 * Class User
 * @package addons\unidrink\model
 */
class User extends Model
{
    // 表名
    protected $name = 'user';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getAvatarAttr($value)
    {
        $value = $value != '' ? $value : Config::getByName('avatar')['value'];
        return Config::getImagesFullUrl($value);
    }

    public function getUsernameAttr($value, $data) {
        return $data['username'] ? $data['username'] : __('Tourist');
    }

    /**
     * 处理一下生日
     */
    public function getBirthdayAttr($value) {
        return empty($value) ? date('Y-m-d H:i:s') : $value;
    }

    /**
     * 关联扩展用户表
     */
    public function extend()
    {
        return $this->hasOne('user_extend', 'user_id', 'id');
    }
}
