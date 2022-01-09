define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    // 合并格式化方法
    Table.api.formatter = $.extend(Table.api.formatter,
        {
            statusCustom : function (value, row, index) {
                let number = value == 0 ? 0 : 1;
                let display = value == 0 ? '否' : '是';
                let color = value == 0 ? 'primary' : 'success';
                var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> ' + display + '</span>';
                if (value != 0){
                    html = '<a href="javascript:;" class="searchit" data-operate="=" data-field="' + this.field + '" data-value="' + number + '" data-toggle="tooltip" title="' + __('Time: %s', Moment(parseInt(value) * 1000).format('YYYY-MM-DD HH:mm:ss')) + '" >' + html + '</a>';
                } else {
                    html = '<a href="javascript:;" class="searchit" data-operate="=" data-field="' + this.field + '" data-value="' + number + '" data-toggle="tooltip" title="' + __('Click to search %s', display) + '" >' + html + '</a>';
                }
                return html;
            }
        }
    );
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unidrink/order/index' + location.search,
                    add_url: 'unidrink/order/add',
                    edit_url: 'unidrink/order/edit',
                    del_url: 'unidrink/order/del',
                    multi_url: 'unidrink/order/multi',
                    refund_url: 'unidrink/order/refund',
                    table: 'unidrink_order',
                }
            });

            var table = $("#table");

            // 合并操作方法
            Table.api.events.operate = $.extend(Table.api.events.operate,
                {
                    'click .btn-refund': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        const total_price = row['total_price'];

                        Layer.confirm('确定退款订单号：'+ids+'，共￥'+ total_price + '元',
                            {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                            function (index) {
                                $.ajax({
                                    type: 'GET',
                                    url: $.fn.bootstrapTable.defaults.extend.refund_url + '?id=' + ids,
                                    contentType: 'application/json',
                                    dataType: 'json',
                                    success: function (res) {
                                        if(res.code == 1) {
                                            Toastr.success(res.msg);
                                            table.bootstrapTable('refresh');
                                        } else {
                                            Toastr.error(res.msg);
                                        }
                                    },
                                    fail:function (){
                                        Toastr.error('退款失败');
                                    }
                                });
                                Layer.close(index);
                            }
                        );

                        //row = $.extend({}, row ? row : {}, {ids: ids});
                        //var url = options.extend.copy_url;
                        //Fast.api.open(Table.api.replaceurl(url, row, table), __('Copy'), $(this).data() || {});
                    }
                }
            );

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns:true,
                fixedRightNumber:1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), visible:false},
                        {field: 'shop_id', title: __('Shop_id'),visible:false},
                        {field: 'shop.name', title: __('Shop name')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'out_trade_no', title: __('Out_trade_no')},
                        {field: 'order_price', title: __('Order_price'), operate:'BETWEEN'},
                        {field: 'discount_price', title: __('Discount_price'), operate:'BETWEEN'},
                        {field: 'delivery_price', title: __('Delivery_price'), operate:'BETWEEN'},
                        {field: 'total_price', title: __('Total_price'), operate:'BETWEEN'},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"5":'余额支付',"4":'支付宝支付',"3":'微信支付'}, formatter: Table.api.formatter.normal},
                        {field: 'type', title: __('Type'), searchList: {"1":__('Type 1'),"2":__('Type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'ip', title: __('Ip'),visible:false},
                        {field: 'remark', title: __('Remark'), visible:false},
                        {field: 'gettime', title: '预约取餐时间', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"-1": '退款',"0": '取消订单',"1": '正常'}, formatter: Table.api.formatter.status},
                        {field: 'have_paid_status', title: '支付', searchList: {"0":__('No'),"1":__('Yes')}, formatter: Table.api.formatter.statusCustom},
                        {field: 'have_made_status', title: '出单/配送',searchList: {"0":__('No'),"1":__('Yes')}, formatter: Table.api.formatter.statusCustom},
                        {field: 'have_received_status', title: '签收',searchList: {"0":__('No'),"1":__('Yes')}, formatter: Table.api.formatter.statusCustom},
                        //{field: 'have_commented_status', title: '评论',searchList: {"0":__('No'),"1":__('Yes')}, formatter: Table.api.formatter.statusCustom},
                        {field: 'have_paid', title: '支付', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,visible:false},
                        {field: 'have_made', title: '出单/配送', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,visible:false},
                        {field: 'have_received', title: '签收', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,visible:false},
                        //{field: 'have_commented', title: '评论', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,visible:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table, events:
                            Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name: 'refund',
                                    text: '退款',
                                    classname: 'btn btn-xs btn-info btn-refund',
                                    extend: 'data-toggle="tooltip"',
                                    icon: 'fa fa-commenting'
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'unidrink/order/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'out_trade_no', title: __('Out_trade_no')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'unidrink/order/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'unidrink/order/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
