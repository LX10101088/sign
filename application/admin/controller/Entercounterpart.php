<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\exception\DbException;
use think\response\Json;

/**
 * 相对方管理
 *
 * @icon fa fa-circle-o
 */
class Entercounterpart extends Backend
{

    /**
     * Entercounterpart模型对象
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
                ->with('enter')
                ->where($where)
                ->where('ownerType','=','enterprise')
                ->where('owner_id','=',$enterId)
                ->where('type','enterprise')
                ->order($sort, $order)
                ->paginate($limit);
        }elseif($this->auth->usertype == 'service'){
            $list = $this->model
                ->with('enter')
                ->where($where)
                ->where('type','enterprise')
                ->order($sort, $order)
                ->paginate($limit);
        }else{
            $list = $this->model
                ->with('enter')
                ->where($where)
                ->where('type','enterprise')
                ->order($sort, $order)
                ->paginate($limit);
        }

        foreach($list as $k=>$v){
            if($v['enter']['attestation'] == 0){
                $list[$k]['enter']['attestation'] = '未认证';
            }else if($v['enter']['attestation'] == 1){
                $list[$k]['enter']['attestation'] = '已认证';
            }else if($v['enter']['attestation'] == 2){
                $list[$k]['enter']['attestation'] = '已认证';
            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }




}
