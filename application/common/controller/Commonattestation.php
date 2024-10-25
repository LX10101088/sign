<?php

namespace app\common\controller;


use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use think\Controller;
use think\Db;


/**
 * 用户认证公共接口
 */
class Commonattestation extends Controller
{


    public function _initialize()
    {


        parent::_initialize();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 10:09:23
     * ps:个人认证
     */
    public function custom($ids,$url=''){
        $custom = Db::name('custom')->where('id','=',$ids)->find();
//        $lovesigning= new Lovesigning();
//        $res = $lovesigning->userattestationurl($custom['name'],$custom['identityNo'],$custom['phone'],$ids);
        //法大大接口
        $fadada = new Fadada();
        $res = $fadada->userattestationurl($custom['name'],$custom['identityNo'],$custom['phone'],$ids,$url);
        $rest['code'] = 300;
        $rest['msg'] = '未知错误，请稍后重试！';
        $rest['identifyUrl'] = '';
        if($res['code'] == 200){
            $rest['code'] = 200;
            $rest['msg'] = '成功';
//            $data['serialNo'] = $res['serialNo'];
            $data['updatetime'] = time();
            Db::name('custom')->where('id','=',$ids)->update($data);
            $rest['identifyUrl']=$res['identifyUrl'];
        }else{
            if($res['msg'] == '该用户已授权，无需重复操作'){
                //已经认证查询信息
                $commonuser = new Commonuser();
                $commonuser->getapicustom($ids);
                $rest['code'] = 201;
            }
            $rest['msg'] = $res['msg'];
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 10:09:23
     * ps:添加个人用户
     */
//    public function addcustom($ids){
//        $custom = Db::name('custom')->where('id','=',$ids)->find();
//        $lovesigning= new Lovesigning();
//        $res = $lovesigning->adduser($custom['identityNo'],$custom['serialNo']);
//
//        $rest['code'] = 300;
//        $rest['msg'] = '未知错误，请稍后重试！';
//
//        if($res['code'] == 200){
//
//            //添加用户唯一标识码
//            $userdata['account'] = $custom['identityNo'];
//            $userdata['updatetime'] = time();
//            Db::name('custom')->where('id','=',$ids)->update($userdata);
//            //创建印章
//            $data['sealNo'] = $res['sealNo'];
//            $data['createtime'] = time();
//            $data['default'] = 1;
//            $data['state'] = 1;
//            $data['name'] = $custom['name'];
//            $commonsignature = new Commonsignature();
//            $commonsignature->addsignature($data,'custom',$custom['id']);
//            $rest['code'] = 200;
//        }else{
//            $rest['msg'] = $res['msg'];
//        }
//        return $rest;
//    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 10:54:07
     * ps:企业认证
     */
    public function enterprise($ids,$url=''){
        $enter = Db::name('enterprise')->where('id','=',$ids)->find();
//        $lovesigning= new Lovesigning();
//        $res = $lovesigning->enterattestationurl($enter['name'],$enter['proveNo'],$enter['legalName'],$enter['legalNo'],$ids);

        $encu = Db::name('enterprise_custom as e')
            ->join('custom','custom.id = e.custom_id')
            ->where('enterprise_id','=',$enter['id'])
            ->where('custom.attestation','=',1)
            ->order('e.id asc')->find();


        if(!$encu){
            $rest['code'] = 300;
            $rest['msg'] = '企业成员无已认证用户，无法进行企业认证';
            $rest['identifyUrl'] = '';
            return $rest;
        }
        $encudata['userName'] = '';
        $encudata['userIdentType'] = '';
        $encudata['userIdentNo'] = '';
        $encudata['mobile'] = '';
        $encudata['oprIdentMethod'] = '';
        $ClientUserId = '';
        if($encu){
            $custom = Db::name('custom')->where('id','=',$encu['custom_id'])->find();
            $encudata['userName'] = $custom['name'];
            $encudata['userIdentType'] = 'id_card';
            $encudata['userIdentNo'] = $custom['identityNo'];
            $encudata['mobile'] = $custom['phone'];
            $encudata['oprIdentMethod'] = ['face','mobile'];
            $ClientUserId = $custom['identityNo'];
        }
        $fadada = new Fadada();
        $res = $fadada->enterattestationurl($enter['name'],$enter['proveNo'],$enter['legalName'],$encudata,$ClientUserId,$url);
        $rest['code'] = 300;
        $rest['msg'] = '未知错误，请稍后重试！';
        $rest['identifyUrl'] = '';
        if($res['code'] == 200){
            $rest['code'] = 200;
            $rest['msg'] = '成功';
//            $data['serialNo'] = $res['serialNo'];
            $data['updatetime'] = time();
            Db::name('enterprise')->where('id','=',$ids)->update($data);
            $rest['identifyUrl']=$res['identifyUrl'];
        }else{
            $rest['msg'] = $res['msg'];
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 13:09:21
     * ps:添加企业用户
     */
//    public function addenter($ids){
//
//        $enter = Db::name('enterprise')->where('id','=',$ids)->find();
//        $lovesigning= new Lovesigning();
//        $res = $lovesigning->addenter($enter['proveNo'],$enter['serialNo'],$enter['legalName'],$enter['legalNo'],$enter['legalPhone']);
//
//
//        $rest['code'] = 300;
//        $rest['msg'] = '未知错误，请稍后重试！';
//
//        if($res['code'] == 200){
//            //添加用户唯一标识码
//            $userdata['account'] = $enter['proveNo'];
//            $userdata['updatetime'] = time();
//            Db::name('enterprise')->where('id','=',$ids)->update($userdata);
//            //创建印章
//            $data['sealNo'] = $res['sealNo'];
//            $data['createtime'] = time();
//            $data['default'] = 1;
//            $data['state'] = 1;
//            $data['name'] = $enter['name'];
//            $commonsignature = new Commonsignature();
//            $commonsignature->addsignature($data,'enterprise',$enter['id']);
//            $rest['code'] = 200;
//        }else{
//            $rest['msg'] = $res['msg'];
//        }
//
//        return $rest;
//    }
}
