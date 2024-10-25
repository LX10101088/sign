define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'signature/index' + location.search,
                    add_url: 'signature/add',
                    edit_url: 'signature/edit',
                    del_url: 'signature/del',
                    multi_url: 'signature/multi',
                    import_url: 'signature/import',
                    table: 'signature',
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

                searchFormVisible:true,

                columns: [
                    [
                        {checkbox: true},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                        //     buttons:[
                        //         // {
                        //         //     name:'setdefault',
                        //         //     text:'设为默认',
                        //         //     title:'设为默认',
                        //         //     classname: 'btn btn-xs btn-info btn-view btn-ajax',
                        //         //     icon: 'fa ',
                        //         //     url: 'signature/setdefault',
                        //         //     refresh:true
                        //         //     // extend:'data-area=\'["100%","100%"]\'',
                        //         // },
                        //
                        //         // {
                        //         //     name:'getstate',
                        //         //     text:'查询状态',
                        //         //     title:'查询状态',
                        //         //     classname: 'btn btn-xs btn-info btn-view btn-ajax',
                        //         //     icon: 'fa ',
                        //         //     url: 'signature/getstate',
                        //         //     refresh:true
                        //         //     // extend:'data-area=\'["100%","100%"]\'',
                        //         // },
                        //
                        //     ],formatter: Table.api.formatter.operate,formatter:
                        //         function(value,row,index){
                        //
                        //             var that = $.extend({},this);
                        //             var table = $(that.table).clone(true);
                        //             if(row.default == '是'){
                        //                 $(table).data(
                        //                     "operate-setdefault",null);
                        //             }
                        //             $(table).data(
                        //                 "operate-del",null);
                        //             $(table).data(
                        //                 "operate-edit",null);
                        //             that.table = table;
                        //             return Table.api.formatter.operate.call(that,value,row,index);
                        //         }
                        // },
                        {field: 'type_id', title: __('Type_id'), operate: false},
                        {field: 'type', title: __('Type'), operate: false},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'sealNo', title: __('SealNo'), operate: 'LIKE'},
                        {field: 'img', title: __('Img'), operate: false,events:Table.api.events.image,formatter:Table.api.formatter.images},
                        {field: 'state', title: __('State'),searchList: {"0":__('制作中'),"1":__('启用'),"2":__('停用')}},
                        // {field: 'default', title: __('Default')},
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
                url: 'signature/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), align: 'left'},
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
                                    url: 'signature/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'signature/destroy',
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
