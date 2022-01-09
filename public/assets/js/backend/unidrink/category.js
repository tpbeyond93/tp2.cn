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
                    index_url: 'unidrink/category/index',
                    add_url: 'unidrink/category/add',
                    edit_url: 'unidrink/category/edit',
                    del_url: 'unidrink/category/del',
                    multi_url: 'unidrink/category/multi',
                    dragsort_url: 'ajax/weigh',
                    table: 'unidrink_category',
                }
            });

            var table = $("#table");
            var tableOptions = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                escape: false,
                pk: 'id',
                sortName: 'weigh',
                sortOrder: 'asc',
                pagination: false,
                commonSearch: false,
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'type', title: __('Type'), searchList: Config.searchList, formatter: Table.api.formatter.normal},
                        {field: 'name', title: __('Name'), align: 'left'},
                        {field: 'shop_text', title: __('Shop'), operate:false, formatter: Table.api.formatter.label},
                        {field: 'flag', title: __('Flag'), operate: false, formatter: Table.api.formatter.flag},
                        {field: 'image', title: __('Image'), operate: false, formatter: Table.api.formatter.image},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'status', title: __('Status'), operate: false, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            };
            // 初始化表格
            table.bootstrapTable(tableOptions);

            // 为表格绑定事件
            Table.api.bindevent(table);

            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // var options = table.bootstrapTable(tableOptions);
                var typeStr = $(this).attr("href").replace('#', '');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    // params.filter = JSON.stringify({type: typeStr});
                    params.type = typeStr;

                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

            });

            //必须默认触发shown.bs.tab事件
            // $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                $(document).on("change", "#c-type", function () {
                    $("#c-pid option[data-type='all']").prop("selected", true);
                    $("#c-pid option").removeClass("hide");
                    $("#c-pid option[data-type!='" + $(this).val() + "'][data-type!='all']").addClass("hide");
                    $("#c-pid").selectpicker("refresh");
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
