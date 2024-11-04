<?php

namespace app\admin\controller\archive;

use app\common\controller\Backend;
use app\common\controller\Commoninfo;
use think\Db;
use think\exception\DbException;
use think\response\Json;

/**
 * 合同管理管理
 *
 * @icon fa fa-circle-o
 */
class Contract extends Backend
{

    /**
     * Contract模型对象
     * @var \app\admin\model\Contract
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Contract;
    }

    public function import()
    {
        parent::import();
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
            $archive = Db::name('contract_archive')->where('type','=','enterprise')->where('type_id','=',$enterId)->select();
            $arlist = '';
            foreach($archive as $k=>$v){
                $arlist .=$v['contract_id'].',';
            }
            $list = $this->model
                ->where($where)
                ->where('initiateType','=','enterprise')
                ->where('initiate_id','=',$enterId)
                ->whereNotIn('id',$arlist)

                ->order($sort, $order)
                ->paginate($limit);
        }elseif($this->auth->usertype == 'service'){
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
        }else{
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
        }
        $common = new Commoninfo();
        foreach($list as $k=>$v){
            if($v['initiateType'] == 'enterprise'){
                $list[$k]['initiateType'] = '企业';
                $list[$k]['initiate_id'] =$common->getenter($v['initiate_id'])['name'];
            }else if($v['initiateType'] == 'custom'){
                $list[$k]['initiateType'] = '个人';
                $list[$k]['initiate_id'] =$common->getcustom($v['initiate_id'])['name'];
            }
            if($v['state'] == 0){
                $list[$k]['state'] = '待签约';
            }else if($v['state'] == 1){
                $list[$k]['state'] = '签约中';
            }else if($v['state'] == 2){
                $list[$k]['state'] = '已签约';
            }else if($v['state'] == 3){
                $list[$k]['state'] = '已过期';
            }else if($v['state'] == 4){
                $list[$k]['state'] = '已拒签';
            }else if($v['state'] == 5){
                $list[$k]['state'] = '未发起';
            }else if($v['state'] == 6){
                $list[$k]['state'] = '已作废';
            }else if($v['state'] == 7){
                $list[$k]['state'] = '已撤销';
            }else if($v['state'] == 10){
                $list[$k]['state'] = '待发起';
            }
            if($v['template'] == 0){
                $list[$k]['template'] = '否';
            }else if($v['template'] == 1){
                $list[$k]['template'] = '是';
            }
            $signing = Db::name('contract_signing')->where('contract_id','=',$v['id'])->select();
            $list[$k]['signinglist'] = '';
            foreach($signing as $kk=>$vv){
                if($vv['type'] == 'enterprise'){
                    $list[$k]['signinglist'] .=  $common->getenter($vv['type_id'],'name')['name'].'；';
                }else{
                    $list[$k]['signinglist'] .=  $common->getcustom($vv['type_id'],'name')['name'].'；';
                }
            }
            $macf = Db::name('contract_macf')->where('contract_id','=',$v['id'])->select();
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
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


}
