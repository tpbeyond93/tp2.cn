<?php

namespace app\admin\validate\unidrink;

use think\Validate;

class Coupon extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'exchange_code' => 'unique:unidrink_coupon'
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'exchange_code.unique' => '兑换码不能重复'
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['exchange_code'],
        'edit' => [''],
    ];

}
