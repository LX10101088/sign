<?php

namespace app\index\controller;

use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use app\common\controller\Common;
use app\common\controller\Commonattestation;
use app\common\controller\Commoncontract;
use app\common\controller\Commonenter;
use app\common\controller\Commonsignature;
use app\common\controller\Commonuser;
use app\common\controller\Frontend;
use think\Db;

/**
 * Created by PhpStorm.
 * User:lang
 * time:2024年9月04月 15:22:02
 * ps:爱签回调方法
 */
class Signnotifyls extends Frontend
{


    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    //认证回调
    public function index()
    {

        //$inputxml = file_get_contents("php://input");
        $inputxml = 'bizContent=%7B%22authScope%22%3A+%5B%22ident_info%22%2C+%22signtask_init%22%2C+%22signtask_info%22%2C+%22signtask_file%22%2C+%22seal_info%22%2C+%22file_storage%22%5D%2C+%22eventTime%22%3A+%221726131373204%22%2C+%22authResult%22%3A+%22success%22%2C+%22openUserId%22%3A+%228893e9d5e2114c679631e2d0227c1262%22%2C+%22identMethod%22%3A+%22mobile%22%2C+%22clientUserId%22%3A+%22211282200001143811%22%2C+%22availableStatus%22%3A+%22enable%22%2C+%22identProcessStatus%22%3A+%22success%22%7D';
        $res=json_decode($inputxml,true);
        dump($res);exit;
        $data['text'] = $inputxml;
        $data['type'] = 1;
        $data['createtime'] = time();
        Db::name('api_log')->insert($data);
        if($res['result'] == 1){
            $edit['attestation'] = 1;
        }else{
            $edit['attestation'] = 2;
        }
        $edit['attestationType'] = $res['type'];
        $edit['updatetime'] = time();
        $edit['finishedTime'] = $res['finishedTime'];
        $commonattestation = new Commonattestation();
        if($res['userType'] == 1){
            //企业
            Db::name('enterprise')->where('proveNo','=',$res['idNo'])->update($edit);
            $enter = Db::name('enterprise')->where('proveNo','=',$res['idNo'])->find();
            //添加企业用户
            $commonattestation->addenter($enter['id']);
            //查询所有跟企业关联的个人用户是否认证
            $encustom = Db::name('enterprise_custom')->where('enterprise_id','=',$enter['id'])->select();
            $common = new Common();
            foreach($encustom as $k=>$v){
                $custom = Db::name('custom')->where('id','=',$v['custom_id'])->find();
                $lovesigning = new Lovesigning();
                $fadata = new Fadada();
                //$res = $lovesigning->getuser($custom['identityNo'],3,2);
                $res = $fadata->getuser($custom['identityNo'],3,2);

                if($res['code'] == 200){
                    if($res['attestation'] == 1){
                        $cudata['serialNo'] = $res['serialNo'];
                        $cudata['finishedTime'] = $res['finishedTime'];
                        $cudata['attestationType'] = $common->getattestationType($res['attestationType']);
                        $cudata['attestation'] = $res['attestation'];
                        $cudata['updatetime'] = time();
                        Db::name('custom')->where('id','=',$v['id'])->update($cudata);
                    }
                }
            }
        }else{
            //个人
            Db::name('custom')->where('identityNo','=',$res['idNo'])->update($edit);
            $custom = Db::name('custom')->where('identityNo','=',$res['idNo'])->find();
            //添加个人用户
            $commonattestation->addcustom($custom['id']);
        }

        $datc['code'] = 200;

        return json_encode($datc);
    }





    //todo 印章操作回调地址
    public function signature()
    {

        $inputxml = file_get_contents("php://input");
        $inputxml = '{"msg":"成功","code":"1","sealNo":"20dav-895ldjaflkjga0-8i393412fda","userType":"1","account":"TC256681MABTHN5D72"}';
        $data['text'] = $inputxml;
        $data['type'] = 2;
        $data['createtime'] = time();
        Db::name('api_log')->insert($data);

        $res=json_decode($inputxml,true);

        if($res['code'] == 1){
            //code等于1代表成功
            if($res['userType'] == 1){
                //企业
                $enter = Db::name('enterprise')->where('proveNo','=',$res['account'])->find();
                $sidata['type'] = 'enterprise';
                $sidata['type_id'] = $enter['id'];
            }else{
                //个人
                $custom = Db::name('custom')->where('identityNo','=',$res['account'])->find();
                $sidata['type'] = 'custom';
                $sidata['type_id'] = $custom['id'];
            }
            $sidata['sealNo'] = $res['sealNo'];
            $sidata['state'] = 1;
            $sign = Db::name('signature')->where('sealNo','=',$res['sealNo'])->find();
            $commonsignature = new Commonsignature();
            if($sign){
                $sidata['updatetime'] = time();
                $commonsignature->editsignature($sidata,$sign['id']);
               $ids = $sign['id'];
            }else{
                $sidata['createtime'] = time();
                $ids = $commonsignature->addsignature($sidata,$sidata['type'],$sidata['type_id']);
            }
            $commonsignature->getplatformsignature($ids);
        }
        $datc['code'] = 200;

        return json_encode($datc);
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 10:18:39
     * ps:合同签署完成回调
     */
    public function signoff(){
        $inputxml = file_get_contents("php://input");
        $data['text'] = $inputxml;
        $data['type'] = 4;
        $data['createtime'] = time();
        Db::name('api_log')->insert($data);
        //查询合同与签约人是否存在，存在继续操作
        $contract = Db::name('contract')->where('contractNo','=',$_GET['contractNo'])->find();
        if($contract){
            $commoncontract = new Commoncontract();
            $commoncontract->getapicontract($contract['id'],1);
        }
        return 'ok';
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 10:18:39
     * ps:合同签署失败回调
     */
    public function signingfailed(){
        $inputxml = file_get_contents("php://input");
        $data['text'] = $inputxml;
        $data['type'] = 5;
        $data['createtime'] = time();
        Db::name('api_log')->insert($data);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 10:18:39
     * ps:单个合同签署完成回调
     */
    public function singlesignoff(){
        $inputxml = file_get_contents("php://input");
        $data['text'] = $inputxml;
        $data['type'] = 6;
        $data['createtime'] = time();
        Db::name('api_log')->insert($data);

        //查询合同与签约人是否存在，存在继续操作
        $contract = Db::name('contract')->where('contractNo','=',$_GET['contractNo'])->find();
        if($contract){
            $signing = Db::name('contract_signing')
                ->where('contract_id','=',$contract['id'])
                ->where('account','=',$_GET['account'])
                ->find();
            if($signing){
                $commoncontract = new Commoncontract();
                $signDatas['account'] = $_GET['account'];

                $signDatas['state'] = $_GET['status'];
                $signDatas['signTime'] = strtotime($_GET['signTime']);
                $commoncontract->operatesigning($contract['id'],$signDatas);
                //修改合同状态
                if($contract['state'] != 2){
                    $conData['state'] = 1;
                    $commoncontract->operatecontract($conData,$contract['initiateType'],$contract['initiate_id'],$contract['id']);
                }
                $commonuser= new Commonuser();
                $commonenter = new Commonenter();
                //获取签署人信息
                if($signing['type'] == 'custom'){
                    //个人
                    $commonuser->getapicustom($signing['type_id']);
                }else{
                    //企业
                    $commonenter->getapienter($signing['type_id']);
                    $encustom = Db::name('enterprise_custom')->where('enterprise_id','=',$signing['type_id'])->find();

                    if($encustom){
                        $commonuser->getapicustom($encustom['custom_id']);
                    }

                }
            }
        }
        return 'ok';
    }
}
