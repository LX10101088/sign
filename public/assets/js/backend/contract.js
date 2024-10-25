define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'contract/index' + location.search,
                    add_url: 'contract/add',
                    edit_url: 'contract/edit',
                    del_url: 'contract/del',
                    multi_url: 'contract/multi',
                    import_url: 'contract/import',
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
                fixedRightNumber: 1,
                searchFormVisible:true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    name:'signing',
                                    text:'签约方',
                                    title:'签约方',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change',
                                    icon: 'fa ',
                                    url: 'signing/index/ids/{ids}/',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },

                                // {
                                //     name:'initiatesigning',
                                //     text:'发起',
                                //     title:'发起',
                                //     classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                //     icon: 'fa ',
                                //     url: 'contract/initiatesigning',
                                //     refresh:true,
                                //     extend:'data-area=\'["100%","100%"]\'',
                                // },
                                // {
                                //     name:'secure',
                                //     text:'解约',
                                //     title:'解约',
                                //     classname: 'btn btn-xs btn-info btn-view btn-ajax',
                                //     icon: 'fa ',
                                //     url: 'contract/secure',
                                //     refresh:true,
                                // },

                                // {
                                //     name:'download',
                                //     text:'下载',
                                //     title:'下载',
                                //     classname: 'btn btn-xs btn-info btn-view',
                                //     icon: 'fa ',
                                //     url: 'contract/download',
                                //     refresh:true,
                                //     extend:'data-area=\'["100%","100%"]\'',
                                // },

                                {
                                    name:'details',
                                    text:'详情',
                                    title:'详情',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                    icon: 'fa ',
                                    url: 'contract/details',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',


                                },
                                // {
                                //     name:'cancel',
                                //     text:'作废',
                                //     title:'作废',
                                //     classname: 'btn btn-xs btn-success btn-dialog btn-change',
                                //     icon: 'fa ',
                                //     url: 'contract/cancel',
                                //     refresh:true,
                                //     extend:'data-area=\'["70%","60%"]\'',
                                // },
                                {
                                    name:'revoke',
                                    text:'撤销',
                                    title:'撤销',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change',
                                    icon: 'fa ',
                                    url: 'contract/revoke',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',

                                },
                                {
                                    name:'initiate',
                                    text:'发起签约',
                                    title:'发起签约',
                                    classname: 'btn btn-xs btn-info btn-view btn-ajax',
                                    icon: 'fa ',
                                    url: 'contract/initiate',
                                    refresh:true,
                                },
                                {
                                    name:'contractdel',
                                    text:'删除',
                                    title:'删除',
                                    classname: 'btn btn-xs btn-info btn-view btn-ajax',
                                    icon: 'fa ',
                                    url: 'contract/del',
                                    refresh:true,
                                },
                                // {
                                //     name:'getcontract',
                                //     text:'查询',
                                //     title:'查询',
                                //     classname: 'btn btn-xs btn-info btn-view btn-ajax',
                                //     icon: 'fa ',
                                //     url: 'contract/getcontract',
                                //     refresh:true,
                                //     // extend:'data-area=\'["100%","100%"]\'',
                                // },
                            ],formatter: Table.api.formatter.operate,formatter:
                                function(value,row,index){

                                    var that = $.extend({},this);
                                    var table = $(that.table).clone(true);
                                    if(row.state != '待发起'){
                                        $(table).data(
                                            "operate-contractdel",null);
                                        $(table).data(
                                            "operate-initiate",null);
                                    }else{
                                        $(table).data(
                                            "operate-signing",null);

                                    }

                                    if(row.state == '已撤销'){
                                        $(table).data(
                                            "operate-revoke",null);
                                    }
                                    if(row.state != '待发起'){
                                        $(table).data(
                                            "operate-initiatesigning",null);
                                    }else{
                                        $(table).data(
                                            "operate-details",null);
                                    }
                                    if(row.state == '已签约'){
                                        $(table).data(
                                            "operate-revoke",null);
                                        $(table).data(
                                            "operate-getcontract",null);
                                    }else{
                                        $(table).data(
                                            "operate-download",null);
                                        $(table).data(
                                            "operate-secure",null);
                                        $(table).data(
                                            "operate-cancel",null);
                                    }
                                    $(table).data(
                                        "operate-del",null);
                                    $(table).data(
                                        "operate-edit",null);
                                    that.table = table;
                                    return Table.api.formatter.operate.call(that,value,row,index);
                                }
                        },
                        // {field: 'initiateType', title: __('InitiateType'),searchList: {"custom":__('个人'),"enterprise":__('企业')}},
                        // {field: 'initiate_id', title: __('Initiate_id'),operate: false},
                        {field: 'contractName', title: __('ContractName'), operate: 'LIKE'},

                        {field: 'contractNo', title: __('ContractNo'), operate: 'LIKE'},
                        {field: 'signinglist', title: __('签约方'), operate: false},
                        // {field: 'signing.type_id', title: __('签约方'), operate: 'LIKE'},

                        {field: 'macf', title: __('抄送方'), operate: false},

                        // {field: 'template_id', title: __('Template_id')},
                        {field: 'contract.state', title: __('State'),searchList: {"0":__('待签约'),"1":__('签约中'),"2":__('已签约'),"3":__('已过期'),"7":__('已撤销'),"10":__('待发起')}},
                        {field: 'template', title: __('Template'),searchList: {"0":__('否'),"1":__('是')}},
                        {field: 'signingTime', title: __('SigningTime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

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
                url: 'contract/recyclebin' + location.search,
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
                                    url: 'contract/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'contract/destroy',
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
        addtecontract: function () {
            Controller.api.bindevent();
        },
        cancel: function () {
            Controller.api.bindevent();
        },
        revoke: function () {
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
