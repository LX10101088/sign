define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'enterprise/index' + location.search,
                    add_url: 'enterprise/add',
                    edit_url: 'enterprise/edit',
                    del_url: 'enterprise/del',
                    multi_url: 'enterprise/multi',
                    import_url: 'enterprise/import',
                    table: 'enterprise',
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
                                    name:'member',
                                    text:'成员',
                                    title:'成员',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'encustom/index',
                                    refresh:true,
                                     extend:'data-area=\'["100%","100%"]\'',
                                },
                                {
                                    name:'attestation',
                                    text:'认证',
                                    title:'认证',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'enterprise/attestation',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },
                                {
                                    name:'enterup',
                                    text:'企业设置',
                                    title:'企业设置',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'platformsetup/edit',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },
                                {
                                    name:'entererwm',
                                    text:'企业入口',
                                    title:'企业入口',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'enterprise/entrance',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },
                                // {
                                //     name:'addsignature',
                                //     text:'添加印章',
                                //     title:'添加印章',
                                //     classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                //     icon: 'fa ',
                                //     url: 'signature/add?ids={ids}&type=enterprise',
                                //     refresh:true,
                                //     // extend:'data-area=\'["100%","100%"]\'',
                                // },
                            ],formatter: Table.api.formatter.operate,formatter:
                                function(value,row,index){
                                    var that = $.extend({},this);
                                    var table = $(that.table).clone(true);
                                    if(row.attestation == '已认证'){
                                        $(table).data(
                                            "operate-attestation",null);
                                    }else{
                                        $(table).data(
                                            "operate-addsignature",null);
                                    }
                                    $(table).data(
                                        "operate-del",null);
                                    $(table).data(
                                        "operate-edit",null);
                                    that.table = table;
                                    return Table.api.formatter.operate.call(that,value,row,index);
                                }
                        },
                        {field: 'service_id', title: __('Service_id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'proveNo', title: __('ProveNo'), operate: 'LIKE'},
                        // {field: 'city', title: __('City'), operate: 'LIKE'},
                        // {field: 'address', title: __('Address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'legalName', title: __('LegalName'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'legalNo', title: __('LegalNo')},
                        {field: 'legalPhone', title: __('LegalPhone'), operate: 'LIKE'},
                        {field: 'attestation', title: __('Attestation'),searchList: {"0":__('未认证'),"1":__('已认证'),"2":__('认证失败')}},
                        {field: 'attestationType', title: __('AttestationType'), operate: false},
                        {field: 'finishedTime', title: __('FinishedTime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
                url: 'enterprise/recyclebin' + location.search,
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
                                    url: 'enterprise/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'enterprise/destroy',
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
