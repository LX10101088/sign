<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Commoninfo;
use think\Db;
use think\exception\DbException;
use think\response\Json;

/**
 * 相对方管理
 *
 * @icon fa fa-circle-o
 */
class Counterpart extends Backend
{

    /**
     * Counterpart模型对象
     * @var \app\admin\model\Counterpart
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Counterpart;

    }

    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        if($this->auth->usertype == 'custom'){
            $enterId = $this->getenter();
            $list = $this->model
                ->with('custom')
                ->where($where)
                ->where('ownerType','=','enterprise')
                ->where('owner_id','=',$enterId)
                ->where('type','=','custom')
                ->order($sort, $order)
                ->paginate($limit);
        }elseif($this->auth->usertype == 'service'){
            $list = $this->model
                ->with('custom')
                ->where($where)
                ->where('type','=','custom')
                ->order($sort, $order)
                ->paginate($limit);
        }else{
            $list = $this->model
                ->with('custom')
                ->where($where)
                ->where('type','=','custom')
                ->order($sort, $order)
                ->paginate($limit);
        }

        foreach($list as $k=>$v){
            if($v['custom']['attestation'] == 0){
                $list[$k]['custom']['attestation'] = '未认证';
            }else if($v['custom']['attestation'] == 1){
                $list[$k]['custom']['attestation'] = '已认证';
            }else if($v['custom']['attestation'] == 2){
                $list[$k]['custom']['attestation'] = '已认证';
            }

            $contract = Db::name('contract as c')
                ->join('contract_signing','c.id = contract_signing.contract_id')
                ->where('initiateType','=',$v['ownerType'])
                ->where('initiate_id','=',$v['owner_id'])
                ->where('contract_signing.type_id','=',$v['type_id'])
                ->order('c.id desc')
                ->find();
            $list[$k]['contractNo'] = $contract['contractNo'];

        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


}
