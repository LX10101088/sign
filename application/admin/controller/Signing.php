<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Common;
use app\common\controller\Commoncontract;
use app\common\controller\Commoninfo;
use think\Db;
use think\exception\DbException;
use think\response\Json;

/**
 * 合同签约人管理
 *
 * @icon fa fa-circle-o
 */
class Signing extends Backend
{

    /**
     * Signing模型对象
     * @var \app\admin\model\Signing
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Signing;

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
            $this->assignconfig("contractId", $ids);

            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $contractId=$this->request->param('contractId');

        $list = $this->model
            ->where($where)
            ->where('contract_id','=',$contractId)
            ->order($sort, $order)
            ->paginate($limit);
        $common = new Common();
        foreach($list as $k=>$v){
            if($v['type'] == 'custom'){
                $list[$k]['type'] = '个人';
                $list[$k]['type_id'] =$common->getcustom($v['type_id'],'name')['name'];
            }else{
                $list[$k]['type'] = '企业';
                $list[$k]['type_id'] =$common->getenter($v['type_id'],'name')['name'];
            }
            if($v['state'] == 0){
                $list[$k]['state'] = '未签署';
            }else{
                $list[$k]['state'] = '已签署';

            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月18月 15:13:53
     * ps:签署
     */
    public function sign($ids){
        $commoncontract = new Commoncontract();
        $redirectUrl ='https://'.$_SERVER['HTTP_HOST'].'/h5/#/pages/login/login?platformId=50&contractId='.$ids;

        $res = $commoncontract->getsignerurl($ids,$redirectUrl);
        $sign = Db::name('contract_signing')->where('id','=',$ids)->find();
        $contract = Db::name('contract')->where('id','=',$sign['contract_id'])->find();
        $commoninfo = new Commoninfo();
        if($contract['initiateType'] == 'enterprise'){
            $contract['initiate_id'] = $commoninfo->getenter($contract['initiate_id'],'name')['name'];
        }else{
            $contract['initiate_id'] = $commoninfo->getcustom($contract['initiate_id'],'name')['name'];
        }
        if($res['code'] == 300){
            $this->error($res['msg']);
        }
        $signlist = Db::name('contract_signing')
            ->where('contract_id','=',$contract['id'])
            ->select();
        $common = new Common();
        $data = '';
        foreach($signlist as $k=>$v){
            if($v['type'] == 'custom'){
                $data .= $common->getcustom($v['type_id'],'name')['name'].';';
            }else{

                $data .= $common->getenter($v['type_id'],'name')['name'].';';
            }
        }
        $common = new Common();
        $url = $common->addqrcode($res['url']);
        $this->assign('data',$data);
        $this->assign('url',$url);
        $this->assign('contract',$contract);

        return $this->view->fetch();

    }
}
