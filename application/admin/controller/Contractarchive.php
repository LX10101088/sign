<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Common;
use think\Db;
use think\exception\DbException;
use think\response\Json;

/**
 * 合同归档管理
 *
 * @icon fa fa-circle-o
 */
class Contractarchive extends Backend
{

    /**
     * Contractarchive模型对象
     * @var \app\admin\model\Contractarchive
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Contractarchive;

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
        if($this->auth->usertype == 'custom') {
            $enterId = $this->getenter();
            $list = $this->model
                ->with(['contract'])
                ->where('contractarchive.type','=','enterprise')
                ->where('type_id','=',$enterId)
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
        }else{
            $list = $this->model
                ->with(['custom'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
        }
        $common = new Common();
        foreach($list as $k=>$v){
            $list[$k]['contract_id'] = $common->getcontract($v['contract_id'],'contractName')['contractName'];
            $list[$k]['archive_id'] = $common->getarchive($v['archive_id'],'name')['name'];
            if($v['contract']['initiateType'] == 'enterprise'){
                $list[$k]['contract']['initiate_id'] = $common->getenter($v['contract']['initiate_id'],'name')['name'];
            }else{
                $list[$k]['contract']['initiate_id'] = $common->getcustom($v['contract']['initiate_id'],'name')['name'];
            }
            $signing = Db::name('contract_signing')->where('contract_id','=',$v['contract']['id'])->select();
            $list[$k]['signing'] = '';
            foreach($signing as $kk=>$vv){
                if($vv['type'] == 'enterprise'){
                    $list[$k]['signing'] .=  $common->getenter($vv['type_id'],'name')['name'].'；';
                }else{
                    $list[$k]['signing'] .=  $common->getcustom($vv['type_id'],'name')['name'].'；';
                }
            }
            $macf = Db::name('contract_macf')->where('contract_id','=',$v['contract']['id'])->select();
            $list[$k]['macf'] = '';
            foreach($macf as $kk=>$vv){
                if($vv['type'] == 'enterprise'){
                    $list[$k]['macf'] .=  $common->getenter($vv['type_id'],'name')['name'].'；';
                }else{
                    $list[$k]['macf'] .=  $common->getcustom($vv['type_id'],'name')['name'].'；';
                }
            }
            if(!$list[$k]['macf']){
                $list[$k]['macf'] = '无';
            }
            if(!$v['contract']['template_id']){
                $list[$k]['contract']['template_id'] = '无';
            }else{
                $list[$k]['contract']['template_id'] = $common->gettemplate($v['contract']['template_id'],'name')['name'];
            }

        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
}
