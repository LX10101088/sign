define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'archive.receivecontract/index' + location.search,
                    add_url: 'archive.receivecontract/add',
                    edit_url: 'archive.receivecontract/edit',
                    del_url: 'archive.receivecontract/del',
                    multi_url: 'archive.receivecontract/multi',
                    import_url: 'archive.receivecontract/import',
                    batch_url: 'archive/batch/type/1/',
                    table: 'contract',
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
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[


                                {
                                    name:'details',
                                    text:'详情',
                                    title:'详情',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'contract/details/ids/{contract_id}/',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },

                                // {
                                //     name:'getcontract',
                                //     text:'查询',
                                //     title:'查询',
                                //     classname: 'btn btn-xs btn-info btn-view btn-ajax',
                                //     icon: 'fa ',
                                //     url: 'contract/getcontract/ids/{contract_id}/',
                                //     refresh:true,
                                //     // extend:'data-area=\'["100%","100%"]\'',
                                // },
                            ],formatter: Table.api.formatter.operate,formatter:
                                function(value,row,index){

                                    var that = $.extend({},this);
                                    var table = $(that.table).clone(true);

                                    if(row.state == '已签署'){
                                        $(table).data(
                                            "operate-signing",null);
                                    }
                                    if(row.contract.state != '待发起'){
                                        $(table).data(
                                            "operate-initiatesigning",null);
                                    }else{
                                        $(table).data(
                                            "operate-details",null);
                                    }
                                    if(row.contract.state == '已签约'){
                                        $(table).data(
                                            "operate-getcontract",null);
                                        $(table).data(
                                            "operate-signing",null);
                                    }else{
                                        $(table).data(
                                            "operate-download",null);
                                        $(table).data(
                                            "operate-secure",null);
                                    }
                                    $(table).data(
                                        "operate-del",null);
                                    $(table).data(
                                        "operate-edit",null);
                                    that.table = table;
                                    return Table.api.formatter.operate.call(that,value,row,index);
                                }
                        },
                        {field: 'contract.initiateType', title: __('InitiateType'),searchList: {"custom":__('个人'),"enterprise":__('企业')}},
                        {field: 'contract.initiate_id', title: __('Initiate_id'),operate: false},
                        {field: 'contract.contractName', title: __('ContractName'), operate: 'LIKE'},
                        {field: 'contract.contractNo', title: __('ContractNo'), operate: 'LIKE'},
                        {field: 'contract.expireTime', title: __('ExpireTime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'template_id', title: __('Template_id')},
                        {field: 'contract.state', title: __('State'),searchList: {"0":__('待签约'),"1":__('签约中'),"2":__('已签约'),"3":__('过期'),"4":__('拒签'),"5":__('未发起'),"6":__('作废'),"7":__('撤销')}},
                        // {field: 'contract.template', title: __('Template'),searchList: {"0":__('否'),"1":__('是')}},
                        {field: 'contract.createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
                url: 'archive.receivecontract/recyclebin' + location.search,
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
                                    url: 'archive.receivecontract/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'archive.receivecontract/destroy',
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
        batch:function () {
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
