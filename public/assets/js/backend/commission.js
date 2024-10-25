define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'commission/index' + location.search,
                    add_url: 'commission/add',
                    edit_url: 'commission/edit',
                    del_url: 'commission/del',
                    multi_url: 'commission/multi',
                    import_url: 'commission/import',
                    table: 'commission',
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
                searchFormVisible:true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    name:'feny',
                                    text:'分佣',
                                    title:'分佣',
                                    classname: 'btn btn-xs btn-info btn-view btn-ajax',
                                    icon: 'fa ',
                                    url: 'commission/confirm',
                                    refresh:true,
                                    confirm: '确认操作？',
                                },
                                {
                                    name:'bfeny',
                                    text:'不分佣',
                                    title:'不分佣',
                                    classname: 'btn btn-xs btn-info btn-view btn-ajax',
                                    icon: 'fa ',
                                    url: 'commission/cancel',
                                    refresh:true,
                                    confirm: '确认操作？',
                                },
                            ],formatter: Table.api.formatter.operate,formatter:
                                function(value,row,index){
                                    var that = $.extend({},this);
                                    var table = $(that.table).clone(true);
                                    if(row.state != '未分佣'){
                                        $(table).data(
                                            "operate-bfeny",null);
                                        $(table).data(
                                            "operate-feny",null);
                                    }
                                    $(table).data(
                                        "operate-del",null);
                                    $(table).data(
                                        "operate-edit",null);
                                    that.table = table;
                                    return Table.api.formatter.operate.call(that,value,row,index);
                                }
                        },
                        {field: 'order.orderNo', title: __('OrderNo')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'service.name', title: __('Name')},
                        {field: 'state', title: __('State'),searchList: {"0":__('未分佣'),"1":__('已分佣'),"2":__('不分佣')}},
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
                url: 'commission/recyclebin' + location.search,
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
                                    url: 'commission/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'commission/destroy',
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
