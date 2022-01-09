//加载Vue 主要用作规格
require.config({
    paths: {
        "vue": "../addons/unidrink/js/vue",
        //"vue": "https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue",
    }
});

define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'upload', 'vue'], function ($, undefined, Backend, Table, Form, Upload, Vue) {

    // 规格模板
    //使用基础 Vue 构造器，创建一个“子类”。参数是一个包含组件选项的对象
    var specComponent = Vue.extend({
        template: '<div>' +
            '        <div class="form-group">\n' +
            '            <label class="control-label col-xs-12 col-sm-2">' + __('use_spec') + '</label>\n' +
            '            <div class="col-xs-12 col-sm-8">\n' +
            '                <input type="radio" name="row[use_spec]" value="0" v-model="use_spec"/>否\n' +
            '                <input type="radio" name="row[use_spec]" value="1" v-model="use_spec"/>是\n' +
            '            </div>\n' +
            '        </div>\n' +
            '\n' +
            '        <div class="form-group" v-if="use_spec == 1">\n' +
            '            <label class="control-label col-xs-12 col-sm-2">' + __('Spec') + '</label>\n' +
            '            <div class="col-xs-12 col-sm-8">\n' +
            '                <input id="c-specList" type="hidden" name="row[specList]" v-model="specListJSON"/>\n' +
            '                <input id="c-specTableList" type="hidden" name="row[specTableList]" v-model="specTableListJSON"/>\n' +
            '\n' +
            '                <table class="table table-hover">\n' +
            '                    <thead>\n' +
            '                    <tr>\n' +
            '                        <th class="col-sm-4">\n' +
            '                            <a href="javascript:;" class="btn btn-success btn-add" title="添加" data-toggle="modal"\n' +
            '                               data-target="#addSpec"><i class="fa fa-plus"></i> 添加属性</a>\n' +
            '                            <!-- 输入规格名称 -->\n' +
            '                            <div class="modal fade" id="addSpec" tabindex="-1" role="dialog">\n' +
            '                                <div class="modal-dialog modal-sm" role="document">\n' +
            '                                    <div class="modal-content">\n' +
            '                                        <div class="modal-body">\n' +
            '                                            <label>输入规格名称</label>\n' +
            '                                            <input type="text" class="form-control" v-model="specName">\n' +
            '                                        </div>\n' +
            '                                        <div class="modal-footer">\n' +
            '                                            <button type="button" class="btn btn-default" data-dismiss="modal">\n' +
            __('Close') +
            '                                            </button>\n' +
            '                                            <button type="button" class="btn btn-primary" data-dismiss="modal"\n' +
            '                                                    v-on:click="addSpecList">' + __('Add') + '\n' +
            '                                            </button>\n' +
            '                                        </div>\n' +
            '                                    </div>\n' +
            '                                </div>\n' +
            '                            </div>\n' +
            '                            <!-- 输入规格值 -->\n' +
            '                            <div class="modal fade" id="addSpecValue" tabindex="-1" role="dialog">\n' +
            '                                <div class="modal-dialog modal-sm" role="document">\n' +
            '                                    <div class="modal-content">\n' +
            '                                        <div class="modal-body">\n' +
            '                                            <label>输入规格 {{specFatherName}} 的值</label>\n' +
            '                                            <input type="text" class="form-control" v-model="specValue">\n' +
            '                                        </div>\n' +
            '                                        <div class="modal-footer">\n' +
            '                                            <button type="button" class="btn btn-default" data-dismiss="modal">\n' +
            __('Close') +
            '                                            </button>\n' +
            '                                            <button type="button" class="btn btn-primary" data-dismiss="modal"\n' +
            '                                                    v-on:click="addSpecChildList">' + __('Add') + '\n' +
            '                                            </button>\n' +
            '                                        </div>\n' +
            '                                    </div>\n' +
            '                                </div>\n' +
            '                            </div>\n' +
            '                            <!-- 批量设置值 -->\n' +
            '                            <div class="modal fade" id="addMultiValue" tabindex="-1" role="dialog">\n' +
            '                                <div class="modal-dialog modal-sm" role="document">\n' +
            '                                    <div class="modal-content">\n' +
            '                                        <div class="modal-body">\n' +
            '                                            <label>批量设置 {{multiName_}} 的值</label>\n' +
            '                                            <input type="number" class="form-control" v-model="multiValue">\n' +
            '                                        </div>\n' +
            '                                        <div class="modal-footer">\n' +
            '                                            <button type="button" class="btn btn-default" data-dismiss="modal">\n' +
            __('Close') +
            '                                            </button>\n' +
            '                                            <button type="button" class="btn btn-primary" data-dismiss="modal"\n' +
            '                                                    v-on:click="addMultiValue">' + __('Multi setting') + '\n' +
            '                                            </button>\n' +
            '                                        </div>\n' +
            '                                    </div>\n' +
            '                                </div>\n' +
            '                            </div>\n' +
            '                        </th>\n' +
            '                        <th style=" height: 50px;\n' +
            '                                    display: block;\n' +
            '                                    line-height: 36px;\n' +
            '                                    text-align: center;">\n' +
            __('Specchildlist') + '\n' +
            '                        </th>\n' +
            '                    </tr>\n' +
            '                    </thead>\n' +
            '                    <tbody>\n' +
            '                    <tr v-for="(item,index) in specList">\n' +
            '                        <td>\n' +
            '                            <div class="input-group">\n' +
            '                                <input type="text" class="form-control" v-model="item.name">\n' +
            '                                <span class="input-group-btn">\n' +
            '                                        <button class="btn btn-default" type="button" v-on:click="delSpec(index)">\n' +
            '                                            <span aria-hidden="true" class="tab-danger">&times;</span>\n' +
            '                                        </button>\n' +
            '                                    </span>\n' +
            '                            </div>\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <div class="input-group" v-for="(childItem, childIndex) in item.child">\n' +
            '                                <input type="text" class="form-control" disabled v-model="item.child[childIndex]">\n' +
            '                                <span class="input-group-btn">\n' +
            '                                        <button class="btn btn-default" type="button"\n' +
            '                                                v-on:click="delSpecChild(index,childIndex)">\n' +
            '                                            <span aria-hidden="true" class="tab-danger">&times;</span>\n' +
            '                                        </button>\n' +
            '                                    </span>\n' +
            '                            </div>\n' +
            '                            <a href="javascript:;" v-on:click="herFatherSpec(index)" class="btn btn-success btn-add"\n' +
            '                               title="添加" data-toggle="modal" data-target="#addSpecValue"><i class="fa fa-plus"></i>\n' +
            '                                添加属性值</a>\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                    </tbody>\n' +
            '                </table>\n' +
            '            </div>\n' +
            '        </div>\n' +
            '        <div class="form-group" v-if="use_spec == 1">\n' +
            '            <div class="col-xs-12 col-sm-1"></div>\n' +
            '            <div class="col-xs-12 col-sm-10">\n' +
            '                <table class="table table-bordered table-hover">\n' +
            '                    <thead>\n' +
            '                    <tr>\n' +
            '                        <th>图片</th>\n' +
            '                        <th v-for="(item) in specList" v-if="item.child.length > 0">\n' +
            '                            <span>\n' +
            '                                {{item.name}}\n' +
            '                            </span>\n' +
            '                        </th>\n' +
            '                        <th><i class="fa fa-plus btn-success" data-toggle="modal" data-target="#addMultiValue" v-on:click="addMultiType(\'code\')"></i>' + __('Code') + '</th>\n' +
            '                        <th><i class="fa fa-plus btn-success" data-toggle="modal" data-target="#addMultiValue" v-on:click="addMultiType(\'market_price\')"></i>' + __('Market price') + '</th>\n' +
            '                        <th><i class="fa fa-plus btn-success" data-toggle="modal" data-target="#addMultiValue" v-on:click="addMultiType(\'sales_price\')"></i>' + __('Sales price') + '</th>\n' +
            '                        <th><i class="fa fa-plus btn-success" data-toggle="modal" data-target="#addMultiValue" v-on:click="addMultiType(\'stock\')"></i>' + __('Stock') + '</th>\n' +
            '                        <th><i class="fa fa-plus btn-success" data-toggle="modal" data-target="#addMultiValue" v-on:click="addMultiType(\'sales\')"></i>' + __('Sales') + '</th>\n' +
            '                    </tr>\n' +
            '                    </thead>\n' +
            '                    <tbody>\n' +
            '                    <tr v-for="(tItem,index) in specTableList">\n' +
            '                        <td>\n' +
            '                            <input v-bind:id="\'c-logoSpec\' + index" type="hidden" v-model="tItem.image" >\n' +
            '\n' +
            '                            <button type="button" v-bind:id="\'fachoose-logoSpec\'+index" class="btn btn-primary fachoose-spec"\n' +
            '                                    v-bind:data-input-id="\'c-logoSpec\'+index" v-bind:data-preview-id="\'p-logoSpec\'+index" data-mimetype="image/*"\n' +
            '                                    data-multiple="false">' + __('Choose') +
            '                            </button>\n' +
            '                            <button style="display: none;" type="button" v-bind:id="\'plupload-logoSpec\'+index"\n' +
            '                                    class="btn btn-danger plupload-spec" v-bind:data-input-id="\'c-logoSpec\'+index"\n' +
            '                                    data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp"\n' +
            '                                    data-multiple="false" v-bind:data-preview-id="\'p-logoSpec\'+index">\n' +
            __('Upload') +
            '                            </button>\n' +
            '                            <ul class="row list-inline plupload-preview width100" v-bind:id="\'p-logoSpec\'+index">\n' +
            '\n' +
            '                            </ul>\n' +
            '                        </td>\n' +
            '                        <td v-for="vItem in tItem.value">\n' +
            '                            <input class="form-control" disabled v-bind:value="vItem" />\n' +
            '                        </td>\n' +
            '                        <td><input class="form-control" data-rule="required" min="0" type="number" v-model="tItem.code"></td>\n' +
            '                        <td><input class="form-control" data-rule="required" min="0" type="number" v-model="tItem.market_price"></td>\n' +
            '                        <td><input class="form-control" data-rule="required" min="0" type="number" v-model="tItem.sales_price"></td>\n' +
            '                        <td><input class="form-control" data-rule="required" min="0" type="number" v-model="tItem.stock"></td>\n' +
            '                        <td><input class="form-control" data-rule="required" min="0" type="number" v-model="tItem.sales"></td>\n' +
            '                    </tr>\n' +
            '                    </tbody>\n' +
            '                </table>\n' +
            '            </div>\n' +
            '        </div>\n' +
            '\n' +
            '        <div class="form-group">\n' +
            '            <label class="control-label col-xs-12 col-sm-2">' + __('Lower market price') + ':</label>\n' +
            '            <div class="col-xs-12 col-sm-8">\n' +
            '                <input id="c-market_price" data-rule="required" v-bind:readonly="use_spec == 1" class="form-control"\n' +
            '                       name="row[market_price]" min="0" type="number" v-model="noSpecValue.market_price">\n' +
            '            </div>\n' +
            '        </div>\n' +
            '        <div class="form-group">\n' +
            '            <label class="control-label col-xs-12 col-sm-2">' + __('Lower sales price') + ':</label>\n' +
            '            <div class="col-xs-12 col-sm-8">\n' +
            '                <input id="c-sales_price" data-rule="required" v-bind:readonly="use_spec == 1" class="form-control"\n' +
            '                       name="row[sales_price]" min="0" type="number" v-model="noSpecValue.sales_price">\n' +
            '            </div>\n' +
            '        </div>\n' +
            '\n' +
            '        <div class="form-group">\n' +
            '            <label class="control-label col-xs-12 col-sm-2">' + __('Total stock') + ':</label>\n' +
            '            <div class="col-xs-12 col-sm-8">\n' +
            '                <input id="c-stock" data-rule="required" v-bind:readonly="use_spec == 1" class="form-control"\n' +
            '                       name="row[stock]" min="0" type="number" v-model="noSpecValue.stock">\n' +
            '            </div>\n' +
            '        </div>\n' +
            '        <div class="form-group">\n' +
            '            <label class="control-label col-xs-12 col-sm-2">' + __('Total sales') + ':</label>\n' +
            '            <div class="col-xs-12 col-sm-8">\n' +
            '                <input id="c-sales" data-rule="required" v-bind:readonly="use_spec == 1" class="form-control"\n' +
            '                       name="row[sales]" min="0" type="number" v-model="noSpecValue.sales">\n' +
            '            </div>\n' +
            '        </div>\n' +
            '</div>\n',
        data() {
            return {
                specList: _specList ? _specList : '',
                //[ //规格列表
                // {
                //     name: '尺寸',
                //     child: ['XS']
                // }, {
                //     name: '大小',
                //     child: ['大', '小']
                // },
                //],
                specTableList: _specTableList ? _specTableList : '',
                //[
                // {
                //     value: ['XS', '大'],
                //     code: 0,
                //     market_price: 0.00,
                //     sales_price: 0.00,
                //     stock: 0,
                //     sales: 0,
                //     image: ''
                // },
                // {
                //     value: ['XS', '小'],
                //     code: 0,
                //     market_price: 0.00,
                //     sales_price: 0.00,
                //     stock: 0,
                //     sales: 0,
                //     image: ''
                // }
                //],
                use_spec: _use_spec ? _use_spec : 0, //是否使用
                specName: '', //要添加的规格健名
                specValue: '', //要添加的规格值
                specChildValue: '', //要添加的规格值
                specFatherIndex: 0,
                market_price: _market_price ? _market_price : 0.00,
                sales_price: _sales_price ? _sales_price : 0.00,
                stock: _stock ? _stock : 0,
                sales: _sales ? _sales : 0,
                multiType:'code', // 当前批量添加的类型
                multiValue: '', // 当前批量编辑的值
                multi: [{ // 批量类型数组
                    'type': 'code',
                    'name': __('Code')
                }, {
                    'type': 'market_price',
                    'name': __('Market price')
                },{
                    'type': 'sales_price',
                    'name': __('Sales price')
                },{
                    'type': 'stock',
                    'name': __('Stock')
                },{
                    'type': 'sales',
                    'name': __('Sales')
                }]
            }
        },
        computed: {
            specListJSON() {
                return JSON.stringify(this.specList);
            },
            specTableListJSON() {
                let data = [];
                // 按字母排序
                for (let i in this.specTableList) {
                    let newData = {};
                    Object.keys(this.specTableList[i]).sort().map(key => {
                        //newData[i][key]=this.specTableList[i].key;
                        newData[key] = this.specTableList[i][key];
                    });
                    data[i] = newData;
                }
                return JSON.stringify(data);
            },
            specFatherName() {
                return this.specList[this.specFatherIndex] ? this.specList[this.specFatherIndex].name : '';
            },
            noSpecValue() {
                if (this.specTableList.length > 0) {
                    let value = this.specTableList[0];

                    let market_price = value.market_price;
                    let sales_price = value.sales_price;
                    let stock = 0;
                    let sales = 0;
                    for (let item of this.specTableList) {
                        market_price = parseFloat(item.market_price) < parseFloat(market_price) ? parseFloat(item.market_price) : market_price;
                        sales_price = parseFloat(item.sales_price) < parseFloat(sales_price) ? parseFloat(item.sales_price) : sales_price;
                        stock = parseFloat(stock) + parseFloat(item.stock);
                        sales = parseFloat(sales) + parseFloat(item.sales);
                    }
                    return {market_price, sales_price, stock, sales};
                } else {
                    return {
                        market_price: this.market_price,
                        sales_price: this.sales_price,
                        stock: this.stock,
                        sales: this.sales,
                    }
                }
            },
            multiName_() {
                let res = '';
                for (let i in this.multi) {
                    if (this.multi[i].type == this.multiType) {
                        res = this.multi[i].name;
                    }
                }
                return res;
            }
        },
        watch: {
            multiValue(val) {
                switch (this.multiType) {
                    case 'code':
                    case 'stock':
                    case 'sales':
                        this.multiValue = parseInt(val);
                        break;
                }
            }
        },
        methods: {
            //渲染到表格
            render() {
                let arr = this.specList;

                if (arr.length == 0) {
                    //空
                    return;
                }
                let specTableList = this.specTableList;
                this.specTableList = [];

                let td = []; //tbody
                var xNum = [];

                // 自创笛卡尔乘积算法 @author: mingwei zheng
                // 主逻辑
                for (var i = 0; i < arr.length; i++) {

                    var valueLenght = arr[i]['child'].length; //是一个数组
                    var cols = cols ? cols : valueLenght;//行数

                    xNum[i] = 1;
                    for (var ii = i + 1; ii < arr.length; ii++) {
                        if (arr[ii]) {
                            let length = arr[ii]['child'].length ? arr[ii]['child'].length : 1;
                            xNum[i] *= length;
                        }
                        if (i == 0) {
                            let length = arr[ii]['child'].length ? arr[ii]['child'].length : 1;
                            cols *= length;
                        }
                    }
                    //console.log(xNum[i]);//每个值填多少个行
                    for (var ii = 0; ii < cols;) {
                        if (arr[i]['child'].length == 0) {
                            ii++;
                        }
                        for (var v = 0; v < arr[i]['child'].length; v++) {
                            for (var x = 0; x < xNum[i]; x++) {
                                td[ii] = td[ii] ? td[ii] : {
                                    value: [],
                                    code: 0,
                                    market_price: 0.00,
                                    sales_price: 0.00,
                                    stock: 0,
                                    sales: 0,
                                    image: ''
                                };
                                td[ii].value.push(arr[i]['child'][v]);

                                //保留原来大值(针对添加的情况)
                                for (let tItem of specTableList) {
                                    if (
                                        tItem.value.toString() + ',' + arr[i]['child'][v] == td[ii].value.toString()
                                        ||
                                        tItem.value.toString() == td[ii].value.toString()
                                    ) {
                                        let tempValue = td[ii].value;
                                        td[ii] = tItem;
                                        td[ii].value = tempValue;
                                        break;
                                    }
                                }
                                ii++;
                            }
                        }
                    }
                }
                this.specTableList = td;
            },
            //添加规格名
            addSpecList() {
                if (this.specName == '') {
                    Toastr.error('不能为空');
                    return
                }
                for (let item of this.specList) {
                    if (item.name == this.specName) {
                        Toastr.error('不能重复');
                        this.specName = '';
                        return
                    }
                }
                if (this.specList === '') {
                    this.specList = [];
                }
                this.specList.push({
                    name: this.specName,
                    child: []
                });
                this.specName = '';
            },
            //添加规格值
            addSpecChildList() {
                if (this.specValue == '') {
                    Toastr.error('不能为空');
                    return
                }
                for (let item of this.specList) {
                    if (item.name == this.specFatherName) {
                        for (let Citem of item.child) {
                            if (Citem == this.specValue) {
                                Toastr.error('不能重复的');
                                this.specValue = '';
                                return
                            }
                        }

                        item.child.push(this.specValue)

                    }
                }
                this.specValue = '';

                //更新到表格
                this.render();
            },
            //设置父亲规格名
            herFatherSpec(index) {
                this.specFatherIndex = index;
            },
            //删除规格属性
            delSpec(index) {
                let confir = confirm('是否删除属性：' + this.specList[index].name + '。若有属性值则一带删除。');
                if (confir) {
                    for (var i = this.specList[index].child.length - 1; i >= 0; i--) {
                        this.delSpecChild(index, i, true);
                    }
                    this.specList.splice(index, 1);

                    this.render();
                }
            },
            //删除规格属性值
            delSpecChild(index, childIndex, noconfir = false) {
                if (!noconfir)
                    var confir = confirm('是否删除属性值：' + this.specList[index].child[childIndex] + '。');
                if (confir || noconfir) {
                    var value = this.specList[index].child.splice(childIndex, 1)[0];
                    //更新到表格
                    //console.log(value)
                    this.renderDel(index, value)
                }
                return;
            },
            //删除渲染
            renderDel(index, value) {
                const that = this;
                if (that.specList[index].child.length == 0) {
                    //删除最后一个的情况。只删除specTableList.value的
                    for (let item of this.specTableList) {
                        for (let i in item.value) {
                            if (item.value[i] == value) {
                                item.value.splice(i, 1);
                                break;
                            }
                        }
                    }
                    this.render();
                } else {
                    //还有属性值的情况。直接删除跟value相关的specTableList
                    for (var i = this.specTableList.length - 1; i >= 0; i--) {
                        for (let item of that.specTableList[i].value) {
                            if (item == value) {
                                this.specTableList.splice(i, 1);
                                break;
                            }
                        }

                    }

                }
                return;

            },
            // 批量添加规格值
            addMultiType(type) {
                this.multiType = type;
                this.multiValue = '';
            },
            addMultiValue() {
                this.multiType; // 当前批量添加的类型
                this.multiValue; // 当前批量编辑的值
                if (this.multiValue === '') {
                    Toastr.error('不能为空');
                    return;
                }
                switch (this.multiType) {
                    case 'market_price':
                    case 'sales_price':
                        this.multiValue = parseFloat(this.multiValue).toFixed(2);
                        break;
                }
                for (let i in this.specTableList) {
                    this.specTableList[i][this.multiType] = this.multiValue;
                }
                this.render();
            }
        }
    });

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unidrink/product/index' + location.search,
                    add_url: 'unidrink/product/add',
                    edit_url: 'unidrink/product/edit',
                    del_url: 'unidrink/product/del',
                    multi_url: 'unidrink/product/multi',
                    evaluate_url: 'unidrink/evaluate/index',
                    copy_url: 'unidrink/product/copy',
                    table: 'unidrink_product',
                }
            });

            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                return "请输入产品名称";
            };

            // 合并操作方法
            Table.api.events.operate = $.extend(Table.api.events.operate,
                {
                    'click .btn-evaluate': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = options.extend.evaluate_url + '?product_id=' + row.id;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Evaluate'), $(this).data() || {});
                    }
                },
                {
                    'click .btn-copy': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = options.extend.copy_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Copy'), $(this).data() || {});
                    }
                }
            );

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
                placeholder: '请输入产品名称',
                fixedColumns:true,
                fixedRightNumber:1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'shop_text', title: __('Shop'), operate:false, formatter: Table.api.formatter.label},
                        {field: 'category_id', title: __('Category_id'), visible: false},
                        {
                            field: 'category', title: __('Category name'), formatter: function (value, row, index) {
                                if (value) {
                                    if (value.parent) {
                                        return value.parent.name + ' / ' + value.name;
                                    } else {
                                        return value.name;
                                    }
                                }
                                return value;
                            }
                        },
                        {field: 'title', title: __('Title')},
                        {
                            field: 'image',
                            title: __('Image'),
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {
                            field: 'images',
                            title: __('Images'),
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.images,
                            visible: false
                        },
                        {field: 'sales_price', title: __('Lower price')},
                        {field: 'stock', title: __('Stock')},
                        {field: 'no_buy_yet', title: '下单未支付'},
                        {field: 'look', title: __('Look')},
                        {field: 'sales', title: __('Sales')},
                        {field: 'real_sales', title: __('Real sales')},
                        {field: 'real_look', title: __('Real look')},
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime,
                            visible: false
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'switch', title: __('Switch'), formatter: Table.api.formatter.toggle},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons:[
                                // {
                                //     name: 'evaluate',
                                //     text: __('Evaluate'),
                                //     classname: 'btn btn-xs btn-info btn-evaluate',
                                //     extend: 'data-toggle="tooltip"',
                                //     icon: 'fa fa-commenting'
                                // },
                                {
                                    name: 'copy',
                                    text: __('Copy'),
                                    classname: 'btn btn-xs btn-info btn-copy',
                                    extend: 'data-toggle="tooltip"',
                                    icon: 'fa fa-copy'
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
                url: 'unidrink/product/recyclebin' + location.search,
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
                                    url: 'unidrink/product/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'unidrink/product/destroy',
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

            var component = new specComponent().$mount();

            // 插入页面
            document.getElementById('spec').appendChild(component.$el);

            $('#spec').on('click', function () {
                // 选择图片
                let chooseImageButtons = $(".fachoose-spec", $("#spec"));
                if (chooseImageButtons) {
                    //console.log(chooseImageButtons);
                    for (let i = 0; i < chooseImageButtons.length; i++) {
                        let events = $._data(chooseImageButtons[i], 'events');
                        if (events && events['click']) {
                            // 已绑定事件
                            //console.log('获取图片不重新绑定事件');
                        } else {
                            $(chooseImageButtons[i]).on('click', function () {
                                var that = this;
                                var multiple = $(this).data("multiple") ? $(this).data("multiple") : false;
                                var mimetype = $(this).data("mimetype") ? $(this).data("mimetype") : '';
                                parent.Fast.api.open("general/attachment/select?element_id=" + $(this).attr("id") + "&multiple=" + multiple + "&mimetype=" + mimetype , __('Choose'), {
                                    callback: function (data) {
                                        var button = $("#" + $(that).attr("id"));
                                        var preview_id = $(button).data("preview-id") ? $(button).data("preview-id") : "";
                                        var input_id = $(button).data("input-id") ? $(button).data("input-id") : "";
                                        if (input_id) {

                                            $("#" + input_id).val(data.url).trigger("change");

                                            //TODO 出发一下input事件，让vue的v-model接收到数据
                                            document.getElementById(input_id).dispatchEvent(new Event('input'));

                                            // 插入图片
                                            if (preview_id && input_id) {
                                                var inputStr = data.url;
                                                var inputArr = inputStr.split(/\,/);
                                                $("#" + preview_id).empty();
                                                var tpl = $("#" + preview_id).data("template") ? $("#" + preview_id).data("template") : "";
                                                $.each(inputArr, function (i, j) {
                                                    if (!j) {
                                                        return true;
                                                    }
                                                    var data = {
                                                        url: j,
                                                        fullurl: Fast.api.cdnurl(j),
                                                        data: $(that).data()
                                                    };
                                                    var html = tpl ? Template(tpl, data) : Template.render(Upload.config.previewtpl, data);
                                                    let preview = $("#" + preview_id);
                                                    preview.append(html);
                                                    $('.btn-trash', preview).on('click', function () {
                                                        preview.empty();
                                                    })
                                                });
                                            }

                                        }
                                    }
                                });
                                return false;
                            });
                        }
                    }
                }

            });

        },
        edit: function () {

            Controller.api.bindevent();

            var component = new specComponent().$mount();

            // 插入页面
            document.getElementById('spec').appendChild(component.$el);

            $('#spec').on('click', function () {
                // 选择图片
                let chooseImageButtons = $(".fachoose-spec", $("#spec"));
                if (chooseImageButtons) {

                    for (let i = 0; i < chooseImageButtons.length; i++) {
                        let events = $._data(chooseImageButtons[i], 'events');

                        let that = chooseImageButtons[i];
                        let button = $("#" + $(that).attr("id"))
                        let maxcount = $(button).data("maxcount");
                        let preview_id = $(button).data("preview-id") ? $(button).data("preview-id") : "";
                        let input_id = $(button).data("input-id") ? $(button).data("input-id") : "";

                        if (events && events['click']) {
                            // 已绑定事件
                            //console.log('获取图片不重新绑定事件');
                        } else {
                            $(chooseImageButtons[i]).on('click', function () {

                                let multiple = $(this).data("multiple") ? $(this).data("multiple") : false;
                                let mimetype = $(this).data("mimetype") ? $(this).data("mimetype") : '';
                                parent.Fast.api.open("general/attachment/select?element_id=" + $(this).attr("id") + "&multiple=" + multiple + "&mimetype=" + mimetype , __('Choose'), {
                                    callback: function (data) {

                                        maxcount = typeof maxcount !== "undefined" ? maxcount : 0;
                                        if (input_id) {

                                            $("#" + input_id).val(data.url).trigger("change");

                                            //TODO 出发一下input事件，让vue的v-model接收到数据
                                            document.getElementById(input_id).dispatchEvent(new Event('input'));

                                            // 插入图片
                                            if (preview_id && input_id) {
                                                let inputStr = data.url;
                                                let inputArr = inputStr.split(/\,/);
                                                $("#" + preview_id).empty();
                                                let tpl = $("#" + preview_id).data("template") ? $("#" + preview_id).data("template") : "";
                                                $.each(inputArr, function (i, j) {
                                                    if (!j) {
                                                        return true;
                                                    }
                                                    let data = {
                                                        url: j,
                                                        fullurl: Fast.api.cdnurl(j),
                                                        data: $(that).data()
                                                    };
                                                    let html = tpl ? Template(tpl, data) : Template.render(Upload.config.previewtpl, data);
                                                    let preview = $("#" + preview_id);
                                                    preview.append(html);
                                                    $('.btn-trash', preview).on('click', function(){
                                                        preview.empty();
                                                        $("#" + input_id).val('');
                                                        //TODO 出发一下input事件，让vue的v-model接收到数据
                                                        document.getElementById(input_id).dispatchEvent(new Event('input'));
                                                    })
                                                });
                                            }
                                        }
                                    }
                                });
                                return false;
                            });
                        }

                        // 首次加载的时候用到, 加载数据
                        let inputStr = $('#' + input_id).val();
                        if (inputStr && input_id) {
                            let inputArr = inputStr.split(/\,/);
                            $("#" + preview_id).empty();
                            let tpl = $("#" + preview_id).data("template") ? $("#" + preview_id).data("template") : "";
                            $.each(inputArr, function (i, j) {
                                if (!j) {
                                    return true;
                                }
                                let data = {
                                    url: j,
                                    fullurl: Fast.api.cdnurl(j),
                                    data: $(that).data()
                                };
                                let html = tpl ? Template(tpl, data) : Template.render(Upload.config.previewtpl, data);
                                //console.log(preview_id);
                                let preview = $("#" + preview_id);
                                preview.append(html);
                                $('.btn-trash', preview).on('click', function(){
                                    $("#" + preview_id).empty();
                                    $('#' + input_id).val('');
                                    //TODO 出发一下input事件，让vue的v-model接收到数据
                                    document.getElementById(input_id).dispatchEvent(new Event('input'));
                                })
                            });
                        }

                    }
                }

            });
            $('#spec').click();

        },
        copy:function(){
            Controller.api.bindevent();

            var component = new specComponent().$mount();

            // 插入页面
            document.getElementById('spec').appendChild(component.$el);

            $('#spec').on('click', function () {
                // 选择图片
                let chooseImageButtons = $(".fachoose-spec", $("#spec"));
                if (chooseImageButtons) {

                    for (let i = 0; i < chooseImageButtons.length; i++) {
                        let events = $._data(chooseImageButtons[i], 'events');

                        let that = chooseImageButtons[i];
                        let button = $("#" + $(that).attr("id"))
                        let maxcount = $(button).data("maxcount");
                        let preview_id = $(button).data("preview-id") ? $(button).data("preview-id") : "";
                        let input_id = $(button).data("input-id") ? $(button).data("input-id") : "";

                        if (events && events['click']) {
                            // 已绑定事件
                            //console.log('获取图片不重新绑定事件');
                        } else {
                            $(chooseImageButtons[i]).on('click', function () {

                                let multiple = $(this).data("multiple") ? $(this).data("multiple") : false;
                                let mimetype = $(this).data("mimetype") ? $(this).data("mimetype") : '';
                                parent.Fast.api.open("general/attachment/select?element_id=" + $(this).attr("id") + "&multiple=" + multiple + "&mimetype=" + mimetype , __('Choose'), {
                                    callback: function (data) {

                                        maxcount = typeof maxcount !== "undefined" ? maxcount : 0;
                                        if (input_id) {

                                            $("#" + input_id).val(data.url).trigger("change");

                                            //TODO 出发一下input事件，让vue的v-model接收到数据
                                            document.getElementById(input_id).dispatchEvent(new Event('input'));

                                            // 插入图片
                                            if (preview_id && input_id) {
                                                let inputStr = data.url;
                                                let inputArr = inputStr.split(/\,/);
                                                $("#" + preview_id).empty();
                                                let tpl = $("#" + preview_id).data("template") ? $("#" + preview_id).data("template") : "";
                                                $.each(inputArr, function (i, j) {
                                                    if (!j) {
                                                        return true;
                                                    }
                                                    let data = {
                                                        url: j,
                                                        fullurl: Fast.api.cdnurl(j),
                                                        data: $(that).data()
                                                    };
                                                    let html = tpl ? Template(tpl, data) : Template.render(Upload.config.previewtpl, data);
                                                    let preview = $("#" + preview_id);
                                                    preview.append(html);
                                                    $('.btn-trash', preview).on('click', function(){
                                                        preview.empty();
                                                        $("#" + input_id).val('');
                                                        //TODO 出发一下input事件，让vue的v-model接收到数据
                                                        document.getElementById(input_id).dispatchEvent(new Event('input'));
                                                    })
                                                });
                                            }
                                        }
                                    }
                                });
                                return false;
                            });
                        }

                        // 首次加载的时候用到, 加载数据
                        let inputStr = $('#' + input_id).val();
                        if (inputStr && input_id) {
                            let inputArr = inputStr.split(/\,/);
                            $("#" + preview_id).empty();
                            let tpl = $("#" + preview_id).data("template") ? $("#" + preview_id).data("template") : "";
                            $.each(inputArr, function (i, j) {
                                if (!j) {
                                    return true;
                                }
                                let data = {
                                    url: j,
                                    fullurl: Fast.api.cdnurl(j),
                                    data: $(that).data()
                                };
                                let html = tpl ? Template(tpl, data) : Template.render(Upload.config.previewtpl, data);
                                //console.log(preview_id);
                                let preview = $("#" + preview_id);
                                preview.append(html);
                                $('.btn-trash', preview).on('click', function(){
                                    $("#" + preview_id).empty();
                                    $('#' + input_id).val('');
                                    //TODO 出发一下input事件，让vue的v-model接收到数据
                                    document.getElementById(input_id).dispatchEvent(new Event('input'));
                                })
                            });
                        }

                    }
                }

            });
            $('#spec').click();

        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },
        select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unidrink/product/index',
                }
            });
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                return "请输入产品名称";
            };

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'id',
                placeholder: '请输入产品名称',
                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'title', title: __('Title')},
                        {field: 'image', title: __('Image'), operate: false, formatter: Table.api.formatter.image},
                        {field: 'stock', title: __('Stock')},
                        {field: 'category.name', title: __('Category name')},
                        {
                            field: 'switch',
                            title: __('Switch'),
                            formatter: Table.api.formatter.status,
                            searchList: {"1": __('Yes'), "0": __('No')}
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true
                        },
                        {
                            field: 'operate', title: __('Operate'), events: {
                                'click .btn-chooseone': function (e, value, row, index) {
                                    var dataArr = new Array();
                                    dataArr.push(row);
                                    Fast.api.close({data: dataArr, multiple: false});
                                },
                            }, formatter: function () {
                                return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                            }
                        }
                    ]
                ]
            });

            // 选中多个
            $(document).on("click", ".btn-choose-multi", function () {
                var dataArr = new Array();
                $.each(table.bootstrapTable("getAllSelections"), function (i, j) {
                    dataArr.push(j);
                });
                var multiple = Backend.api.query('multiple');
                multiple = multiple == 'true' ? true : false;
                Fast.api.close({data: dataArr, multiple: true});
            });

            // 为表格绑定事件
            //Table.api.bindevent(table);
        }
    };
    return Controller;
});
