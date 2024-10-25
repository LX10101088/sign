define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'custom/index' + location.search,
                    add_url: 'custom/add',
                    edit_url: 'custom/edit',
                    del_url: 'custom/del',
                    multi_url: 'custom/multi',
                    import_url: 'custom/import',
                    table: 'custom',
                }
            });

            var table = $("#table");
            $(".btn-add").data("area", ['100%', '100%']);
            $(".btn-edit").data("area", ['100%', '100%']);
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form", table.$commonsearch);
                $("input[name='service_id']", form).addClass("selectpage").data("source", "service/index").data("orderBy", "id desc");
                Form.events.cxselect(form);
                Form.events.selectpage(form);
            });
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
                                    text:'认证',
                                    title:'认证',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'custom/attestation',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },

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
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'identityNo', title: __('IdentityNo'), operate: 'LIKE'},
                        // {field: 'city', title: __('City'), operate: 'LIKE'},
                        {field: 'attestation', title: __('Attestation'),searchList: {"0":__('未认证'),"1":__('已认证'),"2":__('认证失败')}},
                        {field: 'attestationType', title: __('attestationType'), operate: false},

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
                url: 'custom/recyclebin' + location.search,
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
                                    url: 'custom/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'custom/destroy',
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
