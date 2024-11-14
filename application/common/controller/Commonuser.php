<?php

namespace app\common\controller;


use app\admin\model\AuthGroupAccess;
use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use fast\Random;
use think\Controller;
use think\Db;


/**
 * 个人公共接口
 */
class Commonuser extends Controller
{


    public function _initialize()
    {


        parent::_initialize();
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 14:16:31
     * msg:操作个人信息（添加、修改）
     */
    public function operatecustom($data,$customId=0){
        if(!$customId){
            $data['createtime'] = time();
            $customId = Db::name('custom')->insertGetId($data);
            //添加企业后自动创建个人账户并关联企业
            $custom = Db::name('custom')->where('id','=',$customId)->find();

            //创建后台登录账号
            $addata['username'] = $custom['phone'];
            $addata['nickname'] = $custom['name'];
            $addata['avatar'] = '/assets/img/avatar.png';
            $addata['salt'] = Random::alnum();
            $addata['password'] = md5(md5($custom['phone']) . $addata['salt']);
            $addata['user_id'] = $customId;
            $addata['usertype'] = 'custom';
            $addata['createtime'] = time();
            $adminId =Db::name('admin')->insertGetId($addata);
            //todo 进行权限分配
            $groupData['uid']=$adminId;
            $groupData['group_id']=6;
            $AuthGroupAccess = new AuthGroupAccess();
            $AuthGroupAccess->insert($groupData);
            //生成客户钱包
//            $common = new Common();
//            $common->adduseraccount($customId,'custom');
            //绑定销售
            $commonservice = new Commonservice();
            $commonservice->bindservice('custom',$customId);
        }else{
            $data['updatetime'] = time();
            Db::name('custom')->where('id','=',$customId)->update($data);
            //修改后台登录账号信息
            if(isset($data['name'])){
                $addata['nickname'] = $data['name'];
                $addata['updatetime'] = time();
                Db::name('admin')->where('usertype','=','custom')->where('user_id','=',$customId)->update($addata);
            }
        }

        return $customId;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 17:06:27
     * ps:查询个人平台信息并操作信息与印章
     */
    public function getapicustom($ids){
        $custom = Db::name('custom')->where('id','=',$ids)->find();

        $fadada = new Fadada();
        if($custom['attestation'] == 0){
            $res['code'] =300;
            //用户未认证，查询认证状态
            if($custom['account']){

                $res = $fadada->getuser($custom['account'],1,2);
            }else{
                $res = $fadada->getuser($custom['identityNo'],3,2);
            }

                if($res['code'] == 200){
                    //获取个人信息并修改
                    $rescustomdata['account'] = $res['account'];
                    $rescustomdata['name'] = $res['name'];
                    $rescustomdata['identityNo'] = $res['identityNo'];
//                    $rescustomdata['phone'] = $res['phone'];
                    $rescustomdata['attestation'] = $res['attestation'];//认证状态（0：未认证；1：已认证)
                    $rescustomdata['attestationType'] = $res['attestationType'];
                    $rescustomdata['finishedTime'] = $res['finishedTime']/1000;
                    $rescustomdata['serialNo'] = $res['serialNo'];
                    $this->operatecustom($rescustomdata,$custom['id']);
                    //查询印章信息
                    $res = $fadada->getseals($custom['account'],'',2);

                    if($res['code']==200){
                        $commonsignature = new Commonsignature();
                        foreach($res['list'] as $k=>$v){
                            $seal = Db::name('signature')->where('sealNo',$v['sealNo'])->find();
                            $data['sealNo'] = $v['sealNo'];
                            $data['name'] = $v['sealName'];
                            $data['default'] = $v['isDefault'];
                            $data['img'] = $commonsignature->xiazsignature($v['sealUrl'],$v['sealNo']);
                            $data['updatetime'] = time();
                            if($seal){
                                $commonsignature->editsignature($data,$seal['id']);
                            }else{
                                $commonsignature->addsignature($data,'custom',$custom['id']);
                            }
                            if($v['isDefault'] == 1){
                                //其他印章改为不是默认
                                $edit['default'] = 0;
                                $edit['updatetime']=time();
                                Db::name('signature')->where('type','=','custom')->where('type_id','=',$seal['type_id'])->update($edit);
                            }

                        }
                    }

                }

        }else{
            //查询印章信息
            $res = $fadada->getseals($custom['account'],'',2);

            if($res['code']==200){
                $commonsignature = new Commonsignature();
                foreach($res['list'] as $k=>$v){
                    $seal = Db::name('signature')->where('sealNo',$v['sealNo'])->find();
                    $data['sealNo'] = $v['sealNo'];
                    $data['name'] = $v['sealName'];
                    $data['default'] = $v['isDefault'];
                    $data['img'] = $commonsignature->xiazsignature($v['sealUrl'],$v['sealNo']);
                    $data['updatetime'] = time();
                    if($seal){
                        $commonsignature->editsignature($data,$seal['id']);
                    }else{
                        $commonsignature->addsignature($data,'custom',$custom['id']);
                    }
                    if($v['isDefault'] == 1){
                        //其他印章改为不是默认
                        $edit['default'] = 0;
                        $edit['updatetime']=time();
                        Db::name('signature')->where('type','=','custom')->where('type_id','=',$seal['type_id'])->update($edit);
                    }

                }
            }
        }
        //操作企业加入法大大认证
        $encu = Db::name('enterprise_custom')->where('custom_id','=',$custom['id'])->select();
        if($encu){
            $commonenter = new Commonenter();
            foreach($encu as $k=>$v){
                if($v['fadada'] == 0){
                    //添加
                    $commonenter->addmember($v['id']);
                }
            }
        }

        return 1;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月12月 9:33:14
     * ps:更换登录手机号
     * url:{{URL}}/index.php/api/commonuser/updatephone
     */
    public function updatephone($ids,$url = ''){
        $custom = Db::name('custom')->where('id','=',$ids)->find();
        $fadada = new Fadada();
        $res = $fadada->getchangeurl($url);
        return $res;
    }


}
