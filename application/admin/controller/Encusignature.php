<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\exception\DbException;
use think\response\Json;

/**
 * 企业用户授权印章管理
 *
 * @icon fa fa-circle-o
 */
class Encusignature extends Backend
{

    /**
     * Encusignature模型对象
     * @var \app\admin\model\Encusignature
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Encusignature;

    }


    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index($ids=null)
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $this->assignconfig("encuId", $ids);

            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $encuId=$this->request->param('encuId');

        $list = $this->model
            ->with(['signature'])
            ->where($where)
            ->where('encu_id','=',$encuId)
            ->order($sort, $order)
            ->paginate($limit);

        foreach($list as $k=>$v){
            switch($v['state']){
                case 0:
                    $list[$k]['state'] = '启用中';
                    break;
                case 1:
                    $list[$k]['state'] = '已到期';
                    break;

            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }



}
