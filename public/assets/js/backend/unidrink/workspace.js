define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unidrink/workspace/index',
                    add_url: '',
                    edit_url: '',
                    cancel_url: 'unidrink/workspace/cancel',
                    done_url: 'unidrink/workspace/done',
                    multi_url: '',
                }
            });

            var table = $("#table");

            Template.helper("Moment", Moment);

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                templateView: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), visible: false},
                        {field: 'number_id', title: __('Number Id'), visible: false}
                    ]
                ],
                //默认搜索
                search: true,
                //普通表单搜索
                commonSearch: false,
                //可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: false,
                //分页
                pagination: true,
                showExport: false,
                operate: false,
                showColumns: false,
                showToggle: false,
                // 加参数
                queryParams:function(params) {
                    params.shop_id = unidrink_shop_id ? unidrink_shop_id : 0;
                    return params;
                },
                pageSize:15,

            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 设置通用搜索默认文本
            $(".search input").attr('placeholder', '搜索取餐号');

            // 重置取餐号
            $(document).on("click", ".btn-reset", function () {
                var options = table.bootstrapTable('getOptions');
                table.bootstrapTable('refreshOptions', {templateView: !options.templateView});
            });

            //点击详情
            $(document).on("click", ".btn-detail[data-id]", function () {
                Backend.api.open('unidrink/workspace/detail/ids/' + $(this).data('id'), '取餐号'+$(this).data('number_id'));
            });

            // 出单
            $(document).on('click', ".btn-done", function () {
                const id = $(this).data('id');
                const number_id = $(this).data('number_id');
                Layer.confirm('确定完成订单'+number_id+'？',
                    {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                    function (index) {
                        $.ajax({
                            type: 'GET',
                            url: $.fn.bootstrapTable.defaults.extend.done_url + '?id=' + id,
                            contentType: 'application/json',
                            dataType: 'json',
                            success: function (res) {
                                if (res.code == 1) {
                                    load();
                                    Toastr.success('出单成功');
                                } else {
                                    Toastr.error('失败:'+res.msg);
                                }
                            }
                        });
                        Layer.close(index);
                    }
                );
            });

            // 取消订单
            $(document).on('click', ".btn-cancel", function () {
                const id = $(this).data('id');
                const number_id = $(this).data('number_id');
                Layer.confirm('确定取消订单'+number_id+'？',
                    {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                    function (index) {
                        $.ajax({
                            type: 'GET',
                            url: $.fn.bootstrapTable.defaults.extend.cancel_url + '?id=' + id,
                            contentType: 'application/json',
                            dataType: 'json',
                            success: function (res) {
                                if (res.code == 1) {
                                    load();
                                    Toastr.success('取消成功');
                                } else {
                                    Toastr.error('失败:'+res.msg);
                                }
                            }
                        });
                        Layer.close(index);
                    }
                );
            });

            //绑定selectPiker
            var parenttable = table.closest('.bootstrap-table');
            var options = table.bootstrapTable('getOptions');
            var toolbar = $(options.toolbar, parenttable);
            Form.events.selectpicker(toolbar);
            // 绑定selectpiker事件
            // 刷新按钮事件
            $(toolbar).on('change','.selectpicker', function (e) {
                unidrink_shop_id = $(toolbar.selector + ' .selectpicker').val();
            });

            var load = function(){
                table.bootstrapTable('refresh', {silent: true});
                // $.ajax({
                //     type: 'GET',
                //     url: $.fn.bootstrapTable.defaults.extend.index_url + '?shop_id=' + unidrink_shop_id,
                //     contentType: 'application/json',
                //     dataType: 'json',
                //     success: function (res) {
                //         table.bootstrapTable('load', res);
                //         table.trigger('load-success', res);
                //     },
                //     error: function (res) {
                //         table.trigger('load-error', res.status, res);
                //     }
                // });
            };



            // 刷新表格
            setInterval(function () {
                load();
            }, workspace_refresh);

        },
        add: function () {
            Controller.api.bindevent();
        },
        detail: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                url: function (value, row, index) {
                    return '<div class="input-group input-group-sm" style="width:250px;"><input type="text" class="form-control input-sm" value="' + value + '"><span class="input-group-btn input-group-sm"><a href="' + value + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
                },
                ip: function (value, row, index) {
                    return '<a class="btn btn-xs btn-ip bg-success"><i class="fa fa-map-marker"></i> ' + value + '</a>';
                },
                browser: function (value, row, index) {
                    //这里我们直接使用row的数据
                    return '<a class="btn btn-xs btn-browser">' + row.useragent.split(" ")[0] + '</a>';
                }
            },
            events: {
                ip: {
                    'click .btn-ip': function (e, value, row, index) {
                        var options = $("#table").bootstrapTable('getOptions');
                        //这里我们手动将数据填充到表单然后提交
                        $("#commonSearchContent_" + options.idTable + " form [name='ip']").val(value);
                        $("#commonSearchContent_" + options.idTable + " form").trigger('submit');
                        Toastr.info("执行了自定义搜索操作");
                    }
                },
                browser: {
                    'click .btn-browser': function (e, value, row, index) {
                        Layer.alert("该行数据为: <code>" + JSON.stringify(row) + "</code>");
                    }
                }
            }
        }
    };
    return Controller;
});
