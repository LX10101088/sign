<?php

namespace app\admin\controller;

use app\api\controller\Fadada;
use app\common\controller\Backend;
use app\common\controller\Common;
use app\common\controller\Commoncontract;
use app\common\controller\Commonenter;
use app\common\controller\Commoninfo;
use app\common\controller\Commonsignature;
use app\common\controller\Commonuser;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 合同管理管理
 *
 * @icon fa fa-circle-o
 */
class Contract extends Backend
{
    protected $noNeedLogin = ['cancel','revoke','initiate','del'];

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
            $list = $this->model
                ->where($where)
                ->where('deletetime','=',0)
                ->where('initiateType','=','enterprise')
                ->where('initiate_id','=',$enterId)
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
            if($v['template'] == 0){
                $list[$k]['template'] = '否';
            }else if($v['template'] == 1){
                $list[$k]['template'] = '是';
            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        $enterId = $this->getenter();
        $commoninfo = new Commoninfo();
        $account = $commoninfo->getenter($enterId,'account')['account'];
        $commoncontract = new Commoncontract();
        $res = $commoncontract->initiatesigning($account,'enterprise',$enterId);
        if($res['code'] == 300){
            $this->error($res['msg']);
        }
        $this->assign('url',$res['initiateUrl']);
        return $this->view->fetch();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月07月 15:55:45
     * ps:根据模版生成合同
     */
    public function addtecontract($ids){
        $template = Db::name('template')->where('id','=',$ids)->find();
        $content = Db::name('template_content')
            ->where('template_id','=',$template['id'])
            ->where('type','<>',5)
            ->where('type','<>',6)
            ->select();

        $contentpeople = Db::name('template_content')
            ->where('template_id','=',$template['id'])
            ->where('type','<>',1)
            ->where('type','<>',2)
            ->where('type','<>',3)
            ->where('type','<>',4)
            ->where('type','<>',7)
            ->select();
        $commoncontract = new Commoncontract();
        $contractNo = $commoncontract->addcontractNo();
        $account = Db::name('account')->where('type','=',$template['type'])->where('type_id','=',$template['type_id'])->find();

        if (false === $this->request->isPost()) {
            //查询账户余额是否充足
            if($account['contract'] < 1){
                $this->error('账户余额不足，无法生成合同');
            }
            $date = strtotime('+1 year', time());
            $this->assign('contractNo',$contractNo);
            $this->assign('content',$content);
            $this->assign('contentpeople',$contentpeople);
            $this->assign('template',$template);
            $this->assign('date',date('Y-m-d H:i:s',$date));
            return $this->view->fetch();
        }
        $params = $this->request->post('');
       // dump($params);exit;
        //添加合同信息
        $condata['template_id'] = $params['row']['template_id'];
        $condata['contractNo'] = $params['row']['contractNo'];
        $condata['contractName'] = $params['row']['contractName'];
        $condata['expireTime'] = strtotime($params['row']['expireTime']);
        $condata['initiateType'] = $template['type'];
        $condata['initiate_id'] = $template['type_id'];
        $condata['createtime'] = time();
        $condata['state'] = 10;
        $condata['template'] = 1;
        $contractId = $commoncontract->operatecontract($condata,$condata['initiateType'],$condata['initiate_id'] );
        //添加合同签署人
        foreach ($contentpeople as $k=>$v){
            if($v['type'] == 6){
                //企业
                $name = $params[$v['name'].'entername'];
                $phone = $params[$v['name'].'phone'];

                $enter = Db::name('enterprise')->where('name','=',$name)->find();
                $custom = Db::name('custom')->where('phone','=',$phone)->find();
                $commonuser = new Commonuser();
                if(!$custom){
                    $cudata['name'] = $params[$v['name'].'name'];
                    $cudata['phone'] = $params[$v['name'].'phone'];
                    $customId = $commonuser->operatecustom($cudata);
                }else{
                    $customId = $custom['id'];
                }
                $sigdata['custom_id'] = $customId;

                $sigdata['type'] = 'enterprise';
                $sigdata['account'] = $enter['account'];
                if($enter){
                    $sigdata['type_id'] = $enter['id'];
                    $enterId = $enter['id'];
                }else{
                    //没有用户创建用户
                    $enterdata['name'] =  $params[$v['name'].'entername'];
//                    $enterdata['legalName'] =  $params[$v['name'].'name'];
//                    $enterdata['legalPhone'] =  $params[$v['name'].'phone'];
                    $enterdata['createtime'] = time();
                    $commonenter= new Commonenter();
                    $enterId = $commonenter->operateenter($enterdata);
                    $sigdata['type_id'] =$enterId;

                }
                $encu = Db::name('enterprise_custom')->where('enterprise_id','=',$enterId)->where('custom_id','=',$customId)->find();
                if(!$encu){
                    $encudata['enterprise_id'] = $enterId;
                    $encudata['custom_id'] = $customId;
                    $encudata['createtime'] = time();
                    Db::name('enterprise_custom')->insertGetId($encudata);
                }
            }else{
                //个人
                $phone = $params[$v['name'].'phone'];
                $custom = Db::name('custom')->where('phone','=',$phone)->find();
                $sigdata['type'] = 'custom';
                $sigdata['account'] = $custom['account'];
                if($custom){
                    $sigdata['type_id'] = $custom['id'];
//                    $sigdata['phone'] = $custom['phone'];
//                    $sigdata['identityNo'] = $custom['identityNo'];

                }else{
                    //没有用户创建用户
                    $customdata['name'] =  $params[$v['name'].'name'];
                    $customdata['phone'] =  $params[$v['name'].'phone'];
//                    $customdata['identityNo'] =  $params[$v['name'].'identityNo'];
                    $customdata['createtime'] = time();
                    $commonuser = new Commonuser();
                    $customId = $commonuser->operatecustom($customdata);
                    $sigdata['type_id'] = $customId;
                    $sigdata['custom_id'] = $customId;

                }
            }
            $sigdata['state'] = 0;
            $sigdata['contract_id'] = $contractId;
            $sigdata['TCN'] = $v['name'];

            $commoncontract->operatesigning($contractId,$sigdata);


            //添加相对方
            if($sigdata['type_id']==$enterId && $sigdata['type']=='enterprise'){

            }else{
                $counterpart = Db::name('counterpart')
                    ->where('ownerType','=','enterprise')
                    ->where('owner_id','=',$enterId)->find();
                //如果没有相对方就添加
                if(!$counterpart){
                    $cpartdata['ownerType'] = 'enterprise';
                    $cpartdata['owner_id'] = $enterId;
                    $cpartdata['type'] = $sigdata['type'];
                    $cpartdata['type_id'] = $sigdata['type_id'];
                    $cpartdata['createtime'] = time();
                    Db::name('counterpart')->insertGetId($cpartdata);
                }
            }
        }

        //添加合同模版内容
        foreach ($content as $k=>$v){
            $tedata['contract_id'] = $contractId;
            $tedata['name'] = $v['name'];
            $tedata['describe'] = $v['describe'];
            $tedata['type'] = $v['type'];
            $tedata['content'] = $params[$v['name']];
            $tedata['createtime'] = time();
            Db::name('contract_template_content')->insert($tedata);
        }


        $res = $commoncontract->initiatecontract($contractId);
        if($res['code'] == 200){

//            $commoncontract->addSigner($contractId);
            $contractstata['state'] = 0;
            Db::name('contract')->where('id','=',$ids)->update($contractstata);

            //扣除账户合同份数
            $acedit['contract'] = $account['contract'] -1;
            $acedit['usecontract'] = $account['usecontract'] +1;
            $acedit['updatetime'] = time();
            Db::name('account')->where('type','=',$template['type'])->where('type_id','=',$template['type_id'])->update($acedit);



            $this->success('合同发起成功');
        }else{
            $this->error($res['msg']);
        }
    }


    //查询状态
    public function getcontract($ids= null){
        $contract = $this->model->get($ids);
        $commoncontract = new Commoncontract();
//        if($contract['state'] !=10){
            $res = $commoncontract->getapicontract($contract['id'],1);
//        }else{
//            $res = $commoncontract->getapicontract($contract['fileId'],2,$contract['initiateType'],$contract['initiate_id']);
//
//        }

        $this->success('查询完成');
    }
    //发起签约
    public function initiatesigning($ids= null){
        $contract = $this->model->get($ids);

        $this->assign('url',$contract['url']);
        return $this->view->fetch();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 9:30:11
     * ps:解约
     */
    public function secure($ids= null){

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 9:30:11
     * ps:详情
     */
    public function details($ids= null){
        $contract = $this->model->get($ids);
//        if($contract['state'] == 2){
//            $signing = Db::name('contract_signing')->where('contract_id','=',$ids)->find();
//            $url = $signing['signUrl'];
//        }else{
//            $url = $contract['shortUrl'];
//        }
        $fadada = new Fadada();
        $res = $fadada->getpreviewurl($contract['taskId']);
        if($res['code'] == 200){
            $url = $res['url'];
        }else{
            $this->error($res['msg']);
        }
        $this->assign('url',$url);
        return $this->view->fetch();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 9:30:11
     * ps:下载
     */
    public function download($ids= null){
        $commoncontract = new Commoncontract();
        $url = $commoncontract->download($ids);
      //  $contract = Db::name('contract')->where('id','=',$ids)->find();
        if($url){
            $this->assign('url',$url);
            return $this->view->fetch();
        }else{
            $this->error('下载失败');
        }

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 10:21:54
     * ps:作废合同
     */
    public function cancel($ids){
        $row = Db::name('contract')->where('id','=',$ids)->find();
        if($row['expireTime']){
            $row['expireTime'] = date('Y-m-d',$row['expireTime']);
        }
        if ($this->request->isPost()) {
            $reason = $this->request->post('reason');

            $commoncontract = new Commoncontract();
            $res = $commoncontract->cancelcontract($ids,$reason);
            if($res['code'] == 200){
                $this->success('操作成功');
            }else{
                $this->error($res['msg']);
            }
        }

        $this->assign('row',$row);

        return $this->view->fetch();


    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 10:21:54
     * ps:撤销合同
     */
    public function revoke($ids){


        $row = Db::name('contract')->where('id','=',$ids)->find();
        if($row['expireTime']){
            $row['expireTime'] = date('Y-m-d',$row['expireTime']);
        }
        if ($this->request->isPost()) {
            $reason = $this->request->post('reason');
            $commoncontract = new Commoncontract();
            $res = $commoncontract->revokecontract($ids,$reason);

            if($res['code'] == 200){

                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
        $this->assign('row',$row);

        return $this->view->fetch();

    }

    /**
     * 删除
     *
     * @param $ids
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        Db::startTrans();
        try {
            $commoncontract = new Commoncontract();
            foreach ($list as $item) {
                $commoncontract->delcontract($item['id']);
                $count += 1;
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月25月 10:50:52
     * ps:发起签署
     */
    public function initiate($ids=null){
        $commoncontract = new Commoncontract();
        $commoncontract->initiatesign($ids);
        $this->success('发起成功');
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月22月 13:13:45
     * ps:出证
     */
    public function certification($ids){
        $commoncontract = new Commoncontract();
        $rest = $commoncontract->applicationreport($ids);
        if($rest['code']==200){
            $this->assign('url',$rest['url']);

            return $this->view->fetch();
        }else{
            $this->error('网络错误，请稍后重试');
        }



    }
}
