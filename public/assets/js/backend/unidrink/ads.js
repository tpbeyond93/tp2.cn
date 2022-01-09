define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

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

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unidrink/ads/index' + location.search,
                    add_url: 'unidrink/ads/add',
                    edit_url: 'unidrink/ads/edit',
                    del_url: 'unidrink/ads/del',
                    multi_url: 'unidrink/ads/multi',
                    table: 'unidrink_ads',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'switch', title: __('Switch'), searchList: {"0":__('No'),"1":__('Yes')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'shop_text', title: __('Shop'), operate:false, formatter: Table.api.formatter.label},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
