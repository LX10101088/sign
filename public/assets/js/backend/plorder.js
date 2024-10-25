define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'plorder/index' + location.search,
                    add_url: 'plorder/add',
                    edit_url: 'plorder/edit',
                    del_url: 'plorder/del',
                    multi_url: 'plorder/multi',
                    import_url: 'plorder/import',
                    table: 'order',
                }
            });

            var table = $("#table");
            $(".btn-add").data("area", ['100%', '100%']);
            $(".btn-edit").data("area", ['100%', '100%']);
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
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    name:'payment',
                                    text:'线下支付',
                                    title:'线下支付',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'plorder/edit',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },
                                {
                                    name:'payment',
                                    text:'线上支付',
                                    title:'线上支付',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'order/payment/orderId/{row.id}',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },
                            ],formatter: Table.api.formatter.operate,formatter:
                                function(value,row,index){

                                    var that = $.extend({},this);
                                    var table = $(that.table).clone(true);
                                    if(row.state != '待支付'){
                                        $(table).data(
                                            "operate-payment",null);
                                    }
                                    $(table).data(
                                        "operate-del",null);
                                    $(table).data(
                                        "operate-edit",null);
                                    that.table = table;
                                    return Table.api.formatter.operate.call(that,value,row,index);
                                }
                        },
                        {field: 'orderNo', title: __('OrderNo'), operate: 'LIKE'},
                        {field: 'state', title: __('State'),searchList: {"0":__('待确认'),"1":__('待支付'),"2":__('已支付'),"3":__('已完成'),"4":__('已取消')}},
                        {field: 'goods_id', title: __('Goods_id')},
                        {field: 'type', title: __('Type'), operate: 'LIKE'},
                        {field: 'type_id', title: __('Type_id')},
                        {field: 'number', title: __('Number')},
                        {field: 'totalprice', title: __('Totalprice'), operate:'BETWEEN'},
                        {field: 'payway', title: __('Payway'),searchList: {"0":__('线上'),"1":__('线下')}},
                        {field: 'paytime', title: __('Paytime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
                url: 'plorder/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '140px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'plorder/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'plorder/destroy',
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
