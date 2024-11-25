define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'template/index' + location.search,
                    add_url: 'template/add',
                    edit_url: 'template/edit',
                    del_url: 'template/del',
                    multi_url: 'template/multi',
                    import_url: 'template/import',
                    table: 'template',
                }
            });

            var table = $("#table");
            $(".btn-add").data("area", ['100%', '100%']);
            $(".btn-edit").data("area", ['100%', '100%']);
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form", table.$commonsearch);
                $("input[name='classify_id']", form).addClass("selectpage").data("source", "temclassify/index");
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
                                    name:'content',
                                    text:'内容',
                                    title:'内容',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change',
                                    icon: 'fa ',
                                    url: 'templatecontent/index/ids/{ids}/',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },

                                {
                                    name:'contenturl',
                                    text:'详情',
                                    title:'详情',
                                    classname: 'btn btn-xs btn-success btn-dialog btn-change',
                                    icon: 'fa ',
                                    url: 'template/contenturl',
                                    refresh:true,
                                    extend:'data-area=\'["100%","100%"]\'',
                                },
                                {
                                    name:'templatedel',
                                    text:'删除',
                                    title:'删除',
                                    classname: 'btn btn-xs btn-success btn-view btn-ajax',
                                    icon: 'fa ',
                                    url: 'template/del',
                                    refresh:true,
                                },
                                // {
                                //     name:'edits',
                                //     text:'修改',
                                //     title:'修改',
                                //     classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                //     icon: 'fa ',
                                //     url: 'template/edit',
                                //     refresh:true,
                                //     extend:'data-area=\'["40%","50%"]\'',
                                // },
                                // {
                                //     name:'download',
                                //     text:'下载',
                                //     title:'下载',
                                //     classname: 'btn btn-xs btn-info btn-view',
                                //     icon: 'fa ',
                                //     url: 'template/download',
                                //     refresh:true,
                                //     extend:'data-area=\'["100%","100%"]\'',
                                // },
                                // {
                                //     name:'contract',
                                //     text:'发起合同',
                                //     title:'发起合同',
                                //     classname: 'btn btn-xs btn-success btn-dialog btn-change ',
                                //     icon: 'fa ',
                                //     url: 'contract/addtecontract',
                                //     refresh:true,
                                //     extend:'data-area=\'["100%","100%"]\'',
                                // },

                            ],formatter: Table.api.formatter.operate,formatter:
                                function(value,row,index){

                                    var that = $.extend({},this);
                                    var table = $(that.table).clone(true);
                                    if(row.state != '启用中'){
                                        $(table).data(
                                            "operate-contenturl",null);

                                    }
                                    if(row.state !='待生成'){
                                        $(table).data(
                                            "operate-templatedel",null);
                                    }

                                    if(row.typeqx == 1){
                                        $(table).data(
                                            "operate-content",null);
                                    }
                                    $(table).data(
                                        "operate-del",null);
                                    $(table).data(
                                        "operate-edit",null);
                                    that.table = table;
                                    return Table.api.formatter.operate.call(that,value,row,index);
                                }
                        },
                        {field: 'type_id', title: __('Type_id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'templateNo', title: __('TemplateNo'), operate: 'LIKE'},
                        {field: 'classify_id', title: __('分类'), operate: 'LIKE'},

                        // {field: 'file', title: __('File'), operate: false, formatter: Table.api.formatter.file},
                        {field: 'state', title: __('State'),searchList: {"0":__('待生成'),"1":__('生成中'),"2":__('启用中'),"3":__("已停用")}},
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
                url: 'template/recyclebin' + location.search,
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
                                    url: 'template/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'template/destroy',
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
