<?php

namespace app\index\controller;

use app\api\controller\Csms;
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
 * ps:法大大回调方法
 */
class Fadadanotify extends Frontend
{


    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    //认证回调
    public function index()
    {

        if(isset($_SERVER['HTTP_X_FASC_EVENT'])){
            $userAgent = $_SERVER['HTTP_X_FASC_EVENT'];
            $rest = $_POST;


            $contetnt = $rest['bizContent'].'---------'.$userAgent;
            $data['text'] = $contetnt;
            $data['type'] = 1;
            $data['createtime'] = time();
            Db::name('api_log')->insert($data);
            switch($userAgent){
                case 'user-authorize':
                    //个人用户授权
                    $res = json_decode($rest['bizContent'],true);

                    $this->custom($res);
                    break;
                case 'corp-authorize':
                    //企业用户授权
                    $res = json_decode($rest['bizContent'],true);
                    $this->enter($res);
                    break;
                case 'sign-task-signed':
                    //单独一方签署
                    $res = json_decode($rest['bizContent'],true);
                    $this->contract($res);
                    break;
                case 'sign-task-sign-failed':
                    //签署失败
                    $res = json_decode($rest['bizContent'],true);
                    $this->contract($res);
                    break;
                case 'sign-task-sign-rejected':
                    //拒签
                    $res = json_decode($rest['bizContent'],true);
                    $this->contract($res);
                    break;
                case 'sign-task-finished':
                    //签署完成
                    $res = json_decode($rest['bizContent'],true);
                    $this->contract($res,1);
                    break;
                case 'sign-task-expire':
                    $res = json_decode($rest['bizContent'],true);
                    $this->contract($res);
                    //合同过期
                    break;
                case 'sign-task-abolish':
                    //合同作废
                    $res = json_decode($rest['bizContent'],true);
                    $this->contract($res);
                    break;
                case 'seal-create':
                    //印章创建
                    $res = json_decode($rest['bizContent'],true);
                    $this->seal($res);
                    break;
                case 'personal-seal-create':
                    //签名创建

                    $res = json_decode($rest['bizContent'],true);
                    $this->userseal($res);
                    break;
                case 'seal-authorize-member':
                    //印章授权成员事件
                    $res = json_decode($rest['bizContent'],true);
                    $this->sealauthorize($res);
                    break;
            }
        }
        $datc['code'] = 200;
        $datc['msg'] = 'success';

        return json_encode($datc);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月19月 9:50:40
     * ps:个人用户授权
     */
    public function custom($res){

        if($res['authResult'] == 'success'){
            $custom = Db::name('custom')->where('identityNo','=',$res['clientUserId'])->find();
            if($custom){
                $commonuser = new commonuser();
                $commonuser->getapicustom($custom['id']);
            }

        }
        return true;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月19月 9:59:50
     * ps:企业用户授权
     */
    public function enter($res){
        if($res['authResult'] == 'success'){
            $enter = Db::name('enterprise')->where('proveNo','=',$res['clientCorpId'])->find();
            if($enter){
                $commonenter = new Commonenter();
                $commonenter->getapienter($enter['id']);
            }

        }
        return true;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月19月 10:06:56
     * ps:合同操作
     */
    public function contract($res,$state = 0){
        if($res['signTaskId']){
            $contract = Db::name('contract')->where('taskId','=',$res['signTaskId'])->find();
            if($contract){
                $commoncontract = new Commoncontract();
                $commoncontract->getapicontract($contract['id'],1);
                if($state == 1){
                    //签署完成合同
                    $sms = new Csms();
                    $sms->contractfinish($contract['id']);
                }
            }
        }
        return true;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月19月 10:13:09
     * ps:印章操作
     */
    public function seal($res){
        if($res['openCorpId']){
            $enter = Db::name('enterprise')->where('account','=',$res['openCorpId'])->find();
            if($enter){
                $commonenter = new Commonenter();
                $commonenter->getapienter($enter['id']);
            }

        }
        return true;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月19月 10:13:09
     * ps:签名操作
     */
    public function userseal($res){

        if($res['openUserId']){

            $custom = Db::name('custom')->where('account','=',$res['openUserId'])->find();

            if($custom){
                $commonuser = new Commonuser();
                $commonuser->getapicustom($custom['id']);

            }

        }
        return true;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月14月 13:15:40
     * ps:印章授权成员事件
     */
    public function sealauthorize($res){
        if($res['openCorpId']){
            $enter = Db::name('enterprise')->where('account','=',$res['openCorpId'])->find();
            if($enter){
                //如果有企业查询关系表
                $encu = Db::name('enterprise_custom')
                    ->where('enterprise_id','=',$enter['id'])
                    ->where('memberId','=',$res['memberIds'][0])
                    ->find();
                if($encu){
                    $seal = Db::name('signature')->where('sealNo','=',$res['sealIds'][0])->find();
                    $data['encu_id'] = $encu['id'];
                    $data['signature_id'] = $seal['id'];
                    $data['createtime'] =time();
                    $data['starttime'] = time();
                    $data['endtime'] = $res['eventTime'];
                    $ids = Db::name('enterprise_custom_signature')->insertGetId($data);
                    $commonsignature = new Commonsignature();
                    $commonsignature->getsealauthorize($ids);
                }
            }
        }
        return true;
    }



}
