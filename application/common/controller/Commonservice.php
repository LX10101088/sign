<?php

namespace app\common\controller;


use app\admin\model\AuthGroupAccess;
use app\api\controller\Csms;
use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use fast\Random;
use think\Controller;
use think\Db;


/**
 * 合伙人公共接口
 */
class Commonservice extends Controller
{


    public function _initialize()
    {


        parent::_initialize();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月04月 15:39:53
     * ps:根据状态操作合伙人
     */
    public function operatestate($serviceId){
        $service = Db::name('service')->where('id','=',$serviceId)->find();
        if($service['state'] == 1){
            //启用
            $edit['service'] =  1;
        }else{
            $edit['service'] =  0;
        }

        if($service['type'] == 'custom'){
            Db::name('custom')->where('id','=',$service['type_id'])->update($edit);
        }else{
            Db::name('enterprise')->where('id','=',$service['type_id'])->update($edit);
        }

        return true;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月04月 16:12:51
     * ps:销售绑定用户
     */
    public function binduser($filingId){
        $filing = Db::name('service_filing')->where('id','=',$filingId)->find();
        if($filing){
            if(!$filing['type_id']){
                if($filing['type'] == 'custom'){
                    //客户是个人用户
                    $user = Db::name('custom')->where('name','=',$filing['name'])->find();
                    if($user){
                        //修改客户绑定销售
                        $cudata['service_id'] = $filing['service_id'];
                        $cudata['updatetime'] = time();
                        Db::name('custom')->where('id','=',$user['id'])->update($cudata);
                        //添加备案绑定

                    }
                }else{
                    //客户是个人用户
                    $user = Db::name('enterprise')->where('name','=',$filing['name'])->find();
                    if($user){
                        //修改客户绑定销售
                        $endata['updatetime'] = time();

                        $endata['service_id'] = $filing['service_id'];
                        Db::name('enterprise')->where('id','=',$user['id'])->update($endata);
                        //添加备案绑定
                    }
                }
                $fidata['type_id'] = $user['id'];
                $fidata['updatetime'] = time();
                Db::name('service_filing')->where('id','=',$filingId)->update($fidata);
            }
        }
        return true;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月04月 17:24:07
     * ps:客户创建绑定销售
     */
    public function bindservice($type,$typeId){
        if($type == 'custom'){
            $user = Db::name('custom')->where('id','=',$typeId)->find();
        }else{
            $user = Db::name('enterprise')->where('id','=',$typeId)->find();
        }
        if(!$user['service_id']){
            $filing = Db::name('service_filing')->where('name','=',$user['name'])->find();
            if($filing){
                $userdata['service_id'] = $filing['service_id'];
                $userdata['updatetime'] = time();
                if($type == 'custom'){
                    Db::name('custom')->where('id','=',$user['id'])->update($userdata);
                }else{
                    Db::name('enterprise')->where('id','=',$user['id'])->update($userdata);
                }
            }
            $fidata['type_id'] = $user['id'];
            $fidata['updatetime'] = time();
            Db::name('service_filing')->where('id','=',$filing['id'])->update($fidata);
        }
        return true;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月05月 13:58:56
     * ps:根据分佣记录向指定账户添加余额
     */
    public function confirmcommission($ids){
        $com = Db::name('commission')->where('id','=',$ids)->find();
        if($com['state'] == 0 && $com['service_id'] && $com['price']){
            $service = Db::name('service')->where('id','=',$com['service_id'])->find();
            $account = Db::name('account')->where('type_id','=',$service['type_id'])->where('type','=',$service['type'])->find();
            $data['rechargeMoney'] = $account['rechargeMoney']+$com['price'];
            $data['balance'] = $account['balance']+$com['price'];
            $data['updatetime'] = time();
            Db::name('account')->where('id','=',$account['id'])->update($data);
        }
        return true;
    }
}
