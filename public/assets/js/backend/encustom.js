define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'encustom/index/enterId/' + Config.enterId,
                    add_url: 'encustom/add/enterId/' + Config.enterId,
                    edit_url: 'encustom/edit',
                    del_url: 'encustom/del',
                    multi_url: 'encustom/multi',
                    import_url: 'encustom/import',
                    table: 'enterprise_custom',
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
                                    name:'attestation',
                                    text:'实名认证',
                                    title:'实名认证',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'custom/attestation/ids/{$row.custom_id}',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },
                                {
                                    name:'sealaccredit',
                                    text:'印章授权',
                                    title:'印章授权',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'encustom/sealaccredit',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },

                                {
                                    name:'sealmanage',
                                    text:'用印管理',
                                    title:'用印管理',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'encusignature/index',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },

                            ],formatter: Table.api.formatter.operate,formatter:
                                function(value,row,index){
                                    var that = $.extend({},this);
                                    var table = $(that.table).clone(true);
                                    if(row.custom.attestation == '已认证'){
                                        $(table).data(
                                            "operate-attestation",null);
                                    }else{
                                        $(table).data(
                                            "operate-addsignature",null);
                                    }

                                    if(row.dqauth != 1){
                                        $(table).data(
                                            "operate-sealaccredit",null);
                                    }
                                    if(row.purview == 1){
                                        $(table).data(
                                            "operate-sealaccredit",null);
                                        $(table).data(
                                            "operate-sealmanage",null);
                                    }
                                    if(row.purview == 0){
                                        $(table).data(
                                            "operate-sealaccredit",null);
                                        $(table).data(
                                            "operate-sealmanage",null);
                                    }
                                    $(table).data(
                                        "operate-del",null);
                                    $(table).data(
                                        "operate-edit",null);
                                    that.table = table;
                                    return Table.api.formatter.operate.call(that,value,row,index);
                                }
                            // visible:false
                        },
                        // {field: 'service_id', title: __('Service_id')},
                        {field: 'custom.name', title: __('Name'), operate: 'LIKE',


                        },
                        {field: 'custom.phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'custom.identityNo', title: __('IdentityNo'), operate: 'LIKE'},
                        {field: 'custom.attestation', title: __('Attestation'),searchList: {"0":__('未认证'),"1":__('已认证'),"2":__('认证失败')}},
                        {field: 'custom.attestationType', title: __('attestationType'), operate: false},

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
                url: 'encustom/recyclebin' + location.search,
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
                                    url: 'encustom/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'encustom/destroy',
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
        sealaccredit: function () {
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
