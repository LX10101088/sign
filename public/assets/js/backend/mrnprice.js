define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mrnprice/index' + location.search,
                    add_url: 'mrnprice/add',
                    edit_url: 'mrnprice/edit',
                    del_url: 'mrnprice/del',
                    multi_url: 'mrnprice/multi',
                    import_url: 'mrnprice/import',
                    table: 'mrn_price',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                searchFormVisible:true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'createtime', title: __('初诊日期'),visible:false, operate:'BETWEEN'},
                        {field: 'jzys', title: __('就诊医生'),visible:false,searchList: {"尚红英":__('尚红英')}},
                        {field: 'price', title: __('患者交费'),visible:false,searchList: {"已交费":__('已交费')}},

                        {field: 'mrntwo', title: __('Mrntwo'), operate: 'LIKE'},
                        {field: 'bq', title: __('标签'), operate: 'LIKE'},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'sex', title: __('Sex'),searchList: {"男":__('男'),"女":__('女')}},
                        {field: 'age', title: __('Age'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'price', title: __('实收金额'), operate: false},
                        {field: 'jzys', title: __('就诊医生'), operate: false,searchList: {"尚红英":__('尚红英')}},
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
