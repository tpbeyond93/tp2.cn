define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined) {

    var Controller = {
        index: function () {


            // 基于准备好的dom，初始化echarts实例
            var orderNum = Echarts.init(document.getElementById('orderNum'), 'walden');
            var orderMoney = Echarts.init(document.getElementById('orderMoney'), 'walden');

            // 指定图表的配置项和数据
            var optionOrderNum = {
                title: {
                    text: '',
                    subtext: ''
                },
                color: [
                    "#18d1b1",
                    "#3fb1e3",
                    "#626c91",
                    "#a0a7e6",
                    "#c4ebad",
                    "#96dee8"
                ],
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: [__('Order quantity')]
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: [] // 日期
                },
                yAxis: {},
                grid: [{
                    left: 'left',
                    top: 'top',
                    right: '10',
                    bottom: 30
                }],
                series: [{
                    name: __('Order quantity'),
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: [] // 数据
                }]
            };

            // 指定图表的配置项和数据
            var optionOrderMoney = {
                title: {
                    text: '',
                    subtext: ''
                },
                color: [
                    "#18d1b1",
                    "#3fb1e3",
                    "#626c91",
                    "#a0a7e6",
                    "#c4ebad",
                    "#96dee8"
                ],
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: [__('Total amount')]
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: [] // 日期
                },
                yAxis: {},
                grid: [{
                    left: 'left',
                    top: 'top',
                    right: '10',
                    bottom: 30
                }],
                series: [{
                    name: __('Total amount'),
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: [] // 数据
                }]
            };


            // 收入明细表格
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    income_url: 'unidrink/dashboard/income' + location.search,
                    goods_url: 'unidrink/dashboard/goods' + location.search,
                },
                exportOptions: {
                    ignoreColumn: ['xxx'] //全导出
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.income_url,
                pk: '',
                sortName: 'date_time',
                sortOrder:'desc',
                //可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: true,
                commonSearch: true, //是否启用通用搜索
                search:false,
                columns: [
                    [
                        {field: 'date_time', title: __('Date'), formatter: Table.api.formatter.datetime, datetimeFormat: 'YYYY-MM-DD', operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'shop_id', title: __('Shop'), searchList: Config.shopList,visible: false},
                        {field: 'shop_name', title: __('Shop'), operate: false},
                        {field: 'order_nums', title: __('Order quantity'), operate:false},
                        {field: 'order_amount', title: __('Total amount'), operate:false},
                    ]
                ],
                onLoadSuccess:function(res) {

                    optionOrderNum.xAxis.data = res.join_date
                    optionOrderNum.series[0].data = res.order_nums

                    optionOrderMoney.xAxis.data = res.join_date
                    optionOrderMoney.series[0].data = res.order_amount
                    // 使用刚指定的配置项和数据显示图表。
                    orderNum.setOption(optionOrderNum);
                    orderMoney.setOption(optionOrderMoney);

                }
            });
            // 为表格绑定事件
            Table.api.bindevent(table);

            var tableGoods = $("#tableGoods");
            // 初始化表格
            tableGoods.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.goods_url,
                pk: '',
                sortName: 'date_time',
                sortOrder:'desc',
                //可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: true,
                commonSearch: true, //是否启用通用搜索
                search:false,
                columns: [
                    [
                        {field: 'date_time', title: __('Date'), formatter: Table.api.formatter.datetime, datetimeFormat: 'YYYY-MM-DD', operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'shop_id', title: __('Shop'), searchList: Config.shopList,formatter: function(value) {
                            for (let index in Config.shopList) {
                                if (index == value) {
                                    return Config.shopList[index];
                                }
                            }
                            return '店铺id:' + value;
                        }},
                        {field: 'product_name', title: __('Goods name'), operate:false},
                        {field: 'sell_total', title: __('Sales'), operate:false},
                    ]
                ]
            });

            // 监听页面大小变化
            $(window).resize(function () {
                orderNum.resize();
                orderMoney.resize();
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };

    return Controller;
});
