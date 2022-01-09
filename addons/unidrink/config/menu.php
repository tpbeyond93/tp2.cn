<?php
/**
 * 菜单配置文件
 */

return [
    [
        "type" => "file",
        "name" => "unidrink",
        "title" => "uniDrink点餐系统",
        "icon" => "fa fa-list",
        "condition" => "",
        "remark" => "",
        "ismenu" => 1,
        "sublist" => [
            /*[
                "type" => "file",
                "name" => "unidrink/evaluate",
                "title" => "商品评价管理",
                "icon" => "fa fa-circle-o",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/evaluate/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/evaluate/recyclebin",
                        "title" => "回收站",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/evaluate/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/evaluate/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/evaluate/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/evaluate/destroy",
                        "title" => "真实删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/evaluate/restore",
                        "title" => "还原",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/evaluate/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ], */
            [
                "type" => "file",
                "name" => "unidrink/address",
                "title" => "用户地址管理",
                "icon" => "fa fa-address-book",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/address/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/address/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/address/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/address/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/address/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/ads",
                "title" => "广告图管理",
                "icon" => "fa fa-buysellads",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/ads/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/ads/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/ads/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/ads/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/ads/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/category",
                "title" => "分类管理",
                "icon" => "fa fa-align-justify",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/category/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/category/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/category/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/category/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/category/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/config",
                "title" => "系统配置",
                "icon" => "fa fa-certificate",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/config/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/config/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/config/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/config/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/config/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/coupon",
                "title" => "优惠券管理",
                "icon" => "fa fa-gratipay",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/coupon/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/coupon/recyclebin",
                        "title" => "回收站",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/coupon/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/coupon/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/coupon/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/coupon/destroy",
                        "title" => "真实删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/coupon/restore",
                        "title" => "还原",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/coupon/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/order",
                "title" => "订单管理",
                "icon" => "fa fa-print",
                "condition" => "",
                "remark" => "1，自取类型的订单出单之后显示“已发货”并“已配送”",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/order/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/order/recyclebin",
                        "title" => "回收站",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/order/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/order/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/order/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/order/destroy",
                        "title" => "真实删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/order/restore",
                        "title" => "还原",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/order/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/product",
                "title" => "产品管理",
                "icon" => "fa fa-product-hunt",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/product/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/product/recyclebin",
                        "title" => "回收站",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/product/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/product/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/product/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/product/destroy",
                        "title" => "真实删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/product/restore",
                        "title" => "还原",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/product/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/service",
                "title" => "我的服务",
                "icon" => "fa fa-server",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/service/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/service/recyclebin",
                        "title" => "回收站",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/service/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/service/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/service/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/service/destroy",
                        "title" => "真实删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/service/restore",
                        "title" => "还原",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/service/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/shop",
                "title" => "门店管理",
                "icon" => "fa fa-shopping-bag",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/shop/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/shop/recyclebin",
                        "title" => "回收站",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/shop/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/shop/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/shop/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/shop/destroy",
                        "title" => "真实删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/shop/restore",
                        "title" => "还原",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/shop/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/shop/area",
                        "title" => "城市选择",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/workspace",
                "title" => "工作台",
                "icon" => "fa fa-table",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/workspace/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/workspace/detail",
                        "title" => "详情",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/bill",
                "title" => "用户账单",
                "icon" => "fa fa-newspaper-o",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/bill/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/bill/recyclebin",
                        "title" => "回收站",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/bill/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/bill/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/bill/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/bill/destroy",
                        "title" => "真实删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/bill/restore",
                        "title" => "还原",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/bill/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/recharge",
                "title" => "充值金额管理",
                "icon" => "fa fa-money",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/recharge/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/recharge/recyclebin",
                        "title" => "回收站",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/recharge/add",
                        "title" => "添加",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/recharge/edit",
                        "title" => "编辑",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/recharge/del",
                        "title" => "删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/recharge/destroy",
                        "title" => "真实删除",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/recharge/restore",
                        "title" => "还原",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/recharge/multi",
                        "title" => "批量更新",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ]
                ]
            ],
            [
                "type" => "file",
                "name" => "unidrink/dashboard",
                "title" => "仪表盘统计",
                "icon" => "fa fa-dashboard",
                "condition" => "",
                "remark" => "",
                "ismenu" => 1,
                "sublist" => [
                    [
                        "type" => "file",
                        "name" => "unidrink/dashboard/index",
                        "title" => "查看",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/dashboard/income",
                        "title" => "营收统计",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                    [
                        "type" => "file",
                        "name" => "unidrink/dashboard/goods",
                        "title" => "商品销量",
                        "icon" => "fa fa-circle-o",
                        "condition" => "",
                        "remark" => "",
                        "ismenu" => 0
                    ],
                ]
            ],
        ]
    ]
];
