<?php

namespace app\admin\controller\archive;

use app\admin\model\Macf;
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
class Copycontract extends Backend
{

    /**
     * Contract模型对象
     * @var \app\admin\model\Macf
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();

        $this->model = new Macf();
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
                ->with(['contract'])
                ->where('contract.deletetime','=',0)
                ->where('macf.type','=','enterprise')
                ->where('macf.type_id','=',$enterId)
                ->where($where)
                ->whereNotIn('contract.id',$arlist)
                ->order($sort, $order)
                ->paginate($limit);

        }elseif($this->auth->usertype == 'service'){
            $list = $this->model
                ->with(['contract'])
                ->where('contract.deletetime','=',0)
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
        }else{
            $list = $this->model
                ->with(['contract'])
                ->where('contract.deletetime','=',0)
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
        }
        $common = new Commoninfo();
        foreach($list as $k=>$v){
            if($v['contract']['initiateType'] == 'enterprise'){
                $list[$k]['contract']['initiateType'] = '企业';
                $list[$k]['contract']['initiate_id'] =$common->getenter($v['contract']['initiate_id'])['name'];
            }else if($v['contract']['initiateType'] == 'custom'){
                $list[$k]['contract']['initiateType'] = '个人';
                $list[$k]['contract']['initiate_id'] =$common->getcustom($v['contract']['initiate_id'])['name'];
            }
            if($v['contract']['state'] == 0){
                $list[$k]['contract']['state'] = '待签约';
            }else if($v['contract']['state'] == 1){
                $list[$k]['contract']['state'] = '签约中';
            }else if($v['contract']['state'] == 2){
                $list[$k]['contract']['state'] = '已签约';
            }else if($v['contract']['state'] == 3){
                $list[$k]['contract']['state'] = '过期';
            }else if($v['contract']['state'] == 4){
                $list[$k]['contract']['state'] = '拒签';
            }else if($v['contract']['state'] == 5){
                $list[$k]['contract']['state'] = '未发起';
            }else if($v['contract']['state'] == 6){
                $list[$k]['contract']['state'] = '作废';
            }else if($v['contract']['state'] == 7){
                $list[$k]['contract']['state'] = '撤销';
            }else if($v['contract']['state'] == 10){
                $list[$k]['contract']['state'] = '待发起';
            }
            if($v['contract']['template'] == 0){
                $list[$k]['contract']['template'] = '否';
            }else if($v['contract']['template'] == 1){
                $list[$k]['contract']['template'] = '是';
            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


}
