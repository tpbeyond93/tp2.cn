define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unidrink/coupon/index' + location.search,
                    add_url: 'unidrink/coupon/add',
                    edit_url: 'unidrink/coupon/edit',
                    del_url: 'unidrink/coupon/del',
                    multi_url: 'unidrink/coupon/multi',
                    table: 'unidrink_coupon',
                }
            });


            // 合并格式化方法
            Table.api.formatter = $.extend(Table.api.formatter,
                {
                    flag: function (value, row, index) {
                        var that = this;
                        value = value === null ? '' : value.toString();
                        var colorArr = {index: 'success', hot: 'warning', recommend: 'danger', 'new': 'info'};
                        //如果字段列有定义custom
                        if (typeof this.custom !== 'undefined') {
                            colorArr = $.extend(colorArr, this.custom);
                        }
                        var field = this.field;
                        if (typeof this.customField !== 'undefined' && typeof row[this.customField] !== 'undefined') {
                            value = row[this.customField];
                            field = this.customField;
                        }

                        //渲染Flag
                        var html = [];
                        var arr = value.split(',');
                        var color, display, label;
                        $.each(arr, function (i, value) {
                            value = value === null ? '' : value.toString();
                            if (value == '')
                                return true;
                            color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                            display = typeof that.searchList !== 'undefined' && typeof that.searchList[value] !== 'undefined' ? that.searchList[value] : __(value.charAt(0).toUpperCase() + value.slice(1));
                            label = '<span class="label label-' + color + '">' + display + '</span></br>';
                            if (that.operate) {
                                html.push('<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', display) + '" data-field="' + field + '" data-value="' + value + '">' + label + '</a>');
                            } else {
                                html.push(label);
                            }
                        });
                        return html.join(' ');
                    }
                }
            );
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                fixedColumns:true,
                fixedRightNumber:1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'shop_id', title: __('Shop_id'),visible:false},
                        {field: 'shop_text', title: __('Shop name'), operate:false, formatter: Table.api.formatter.label},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'title', title: __('Title')},
                        {field: 'switch', title: __('Switch'), searchList: {"1":__('Yes'),"0":__('No')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'least', title: __('Least')},
                        {field: 'value', title: __('Value')},
                        {field: 'receive', title: __('Receive')},
                        {field: 'distribute', title: __('Distribute')},
                        {field: 'limit', title: __('Limit')},
                        {field: 'exchange_code', title: __('Exchange code')},
                        {field: 'type', title: __('Type'), searchList: {"0":__('通用'),"1":__('自取'),"2":__('外卖')}, formatter: Table.api.formatter.status},
                        {field: 'score', title: __('Score')},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, visible: false},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                url: 'unidrink/coupon/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), align: 'left'},
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
                                    url: 'unidrink/coupon/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'unidrink/coupon/destroy',
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
