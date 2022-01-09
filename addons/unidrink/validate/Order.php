<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2019/11/5
 * Time: 11:00 下午
 */

namespace addons\unidrink\validate;

use think\Validate;

class Order extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'product_id' => 'require',
        'number' => 'require',
        'remark' => 'max:250',
        'address_id' => 'require',
        'mobile' => 'require',
        'shop_id' => 'require',
        'type' => 'require',
        'pay_type' => 'require',
        'gettime' => 'require',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'product_id.required' => '产品编号不能为空',
        'number.require' => '商品数量不能为空',
        'remark.max' => '备注不能超过250个文字',
        'address_id.require' => '请选择收货地址',
        'mobile.require' => '联系电话不能为空',
        'shop_id.require' => '请选择店铺',
        'type.require' => '购买类型不能为空',
        'pay_type.require' => '支付类型不能为空',
        'gettime.require' => '取餐时间不能为空',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'submit'  => ['product_id', 'number', 'city_id', 'address_id', 'delivery_id', 'remark', 'gettime'], // 创建订单
    ];

}
