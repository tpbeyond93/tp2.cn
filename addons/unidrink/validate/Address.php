<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2019/11/5
 * Time: 11:00 下午
 */

namespace addons\unidrink\validate;

use think\Validate;

class Address extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'id' => 'require|integer',
        'name' => 'require|max:30',
        'mobile' => 'require|number|max:20',
        'address' => 'require|max:255',
        'lng' => 'require',
        'lat' => 'require',
        'door_number' => 'require',
        'sex' => 'require'
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'name.require' => '名字不能为空',
        'name.max' => '名字不能大于30字',
        'mobile.require' => '电话号码必填',
        'mobile.number' => '电话号码必须为数字',
        'mobile.max' => '电话号码不能大于20字',
        'address.require' => '地址不能为空',
        'address.max' => '地址不能超过255字',
        'lng.require' => '经度不能为空',
        'lat.require' => '纬度不能为空',
        'door_number.require' => '门牌号不能为空',
        'sex.require' => '性别不能为空',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['name', 'mobile', 'address', 'lng', 'lat', 'door_number', 'sex'],
        'edit' => ['id', 'name', 'mobile', 'address', 'lng', 'lat', 'door_number', 'sex'],
    ];

}
