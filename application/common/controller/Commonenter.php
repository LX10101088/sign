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
 * 企业公共接口
 */
class Commonenter extends Controller
{


    public function _initialize()
    {


        parent::_initialize();
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 14:16:31
     * msg:操作企业信息（添加、修改）
     */
    public function operateenter($data,$enterId=0,$cu = 0,$customId=0){
        if(!$enterId){
            $data['createtime'] = time();
            $enterId = Db::name('enterprise')->insertGetId($data);
            //创建企业平台设置信息
            $setup['platformName'] = $data['name'];
            $setup['enterprise_id'] = $enterId;
            $setup['createtime'] = time();
            Db::name('platform_setup')->insertGetId($setup);
            //添加企业后自动创建个人账户并关联企业
            $enter = Db::name('enterprise')->where('id','=',$enterId)->find();
//            $custom = Db::name('custom')->where('id','=',$customId)->find();
//            if($custom){
//                $customId = $custom['id'];
//                $cudata['name'] = $custom['name'];
//                $cudata['phone'] = $custom['phone'];
//                $cu = 1;
//            }else{
//
//            }
            //创建企业法人账号并绑定关系
//            $custom = Db::name('custom')->where('phone','=',$enter['legalPhone'])->find();
//            if($custom){
//                $customId = $custom['id'];
//
//            }else{
//                if(isset($data['legalName'])){
//                    $cudata['name'] = $data['legalName'];
//                    $cudata['phone'] = $data['legalPhone'];
//                    $cudata['identityNo'] = $data['legalNo'];
//
//                    $cudata['createtime'] = time();
//                    $commonuser = new Commonuser();
//                    $customId = $commonuser->operatecustom($cudata);
//                }
//
//            }
//            //创建企业用户与个人用户关系
//            $ecdata['custom_id'] = $customId;
//            $ecdata['enterprise_id'] = $enterId;
//            $ecdata['owner'] = 1;
//
//            $ecdata['createtime'] = time();
//            $ecId = Db::name('enterprise_custom')->insertGetId($ecdata);
            //法大大添加成员
            //$this->addmember($ecId);
            $common = new Common();
            $common->adduseraccount($enterId,'enterprise');
            //发送短信
            $sms = new Csms();
            $sms->newenter($enterId);
//            else{
//                //身份证号信息没有就使用信息查询
//                $custom = Db::name('custom')->where('phone','=',$enter['legalPhone'])->find();
//                if($custom){
//                    $customId = $custom['id'];
//                    $cudata['name'] = $custom['name'];
//                    $cudata['phone'] = $custom['phone'];
//                    $cu =1;
//                }else{
//                    $cudata['name'] = $enter['legalName'];
//                    $cudata['phone'] = $enter['legalPhone'];
//                    $cudata['identityNo'] = $enter['legalNo'];
//                    $cudata['province'] = $enter['province'];
//                    $cudata['city'] = $enter['city'];
//                    $cudata['area'] = $enter['area'];
//                    $cudata['address'] = $enter['address'];
//                    $cudata['createtime'] = time();
//                    $customId = Db::name('custom')->insertGetId($cudata);
//                }
//
//            }

//            if($cu == 0){
//                //创建后台登录账号
//                $addata['username'] = $cudata['phone'];
//                $addata['nickname'] = $cudata['name'];
//                $addata['salt'] = Random::alnum();
//                $addata['password'] = md5(md5($cudata['phone']) . $addata['salt']);
//                $addata['user_id'] = $customId;
//                $addata['createtime'] = time();
//                $adminId =Db::name('admin')->insertGetId($addata);
//                //todo 进行权限分配
//                $groupData['uid']=$adminId;
//                $groupData['group_id']=1;
//                $AuthGroupAccess = new AuthGroupAccess();
//                $AuthGroupAccess->insert($groupData);
//            }
            //绑定销售
            $commonservice = new Commonservice();
            $commonservice->bindservice('enterprise',$enterId);
        }else{
            $data['updatetime'] = time();

            Db::name('enterprise')->where('id','=',$enterId)->update($data);

        }

        return $enterId;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 17:06:27
     * ps:查询企业平台信息并操作信息与印章
     */
    public function getapienter($ids){
        $enterprise = Db::name('enterprise')->where('id','=',$ids)->find();

        //$lovesigning = new Lovesigning();
        $fadada = new Fadada();
        if($enterprise['attestation'] == 0){
            //用户未认证，查询认证状态
            if($enterprise['account']){
                $res = $fadada->getuser($enterprise['account'],1,1);
            }else{
                $res = $fadada->getuser($enterprise['proveNo'],3,1);

            }


                if($res['code'] == 200){
                    //获取个人信息并修改
                    $rescustomdata['account'] = $res['account'];
                    $rescustomdata['legalName'] = $res['name'];
//                    $rescustomdata['legalNo'] = $res['identityNo'];
//                    $rescustomdata['legalPhone'] = $res['phone'];
                    $rescustomdata['attestation'] = $res['attestation'];//认证状态（0：未认证；1：已认证)
                    $rescustomdata['attestationType'] = $res['attestationType'];
                    $rescustomdata['finishedTime'] = $res['finishedTime'];
                    $rescustomdata['proveNo'] = $res['creditCode'];
                    $rescustomdata['name'] = $res['companyName'];


                    $this->operateenter($rescustomdata,$enterprise['id']);

                    //查询印章信息
                    $res = $fadada->getseals($enterprise['account'],'',1);
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
                                $commonsignature->addsignature($data,'enterprise',$enterprise['id']);
                            }
                            if($v['isDefault'] == 1){
                                //其他印章改为不是默认
                                $edit['default'] = 0;
                                $edit['updatetime']=time();
                                Db::name('signature')->where('type','=','enterprise')->where('type_id','=',$seal['type_id'])->update($edit);
                            }

                        }
                    }

                }

        }else{
            //查询印章信息
            $res = $fadada->getseals($enterprise['account'],'',1);
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
                        $commonsignature->addsignature($data,'enterprise',$enterprise['id']);
                    }
                    if($v['isDefault'] == 1){
                        //其他印章改为不是默认
                        $edit['default'] = 0;
                        $edit['updatetime']=time();
                        Db::name('signature')->where('type','=','enterprise')->where('type_id','=',$seal['type_id'])->update($edit);
                    }

                }
            }
        }
        return true;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月13月 13:42:42
     * ps:平台添加成员
     */
    public function addplatemember($enterId,$name,$phone,$identityNo){
        $custom = Db::name('custom')->where('phone','=',$phone)->find();
        $biaos = 0;//标识 0:需要新增；1：不需要操作；2：需要覆盖信息；3：信息不对等；4：成员已存在
        if($custom){
            $biaos = 1;

            //判断是否有信息
            if($custom['name']){
                if($custom['name'] != $name){
                    $biaos = 3;
                }
            }else{
                if($biaos !=3){
                    $biaos = 2;
                }
            }
            if($custom['identityNo']){
                if($custom['identityNo'] != $identityNo){
                    $biaos = 3;
                }
            }else{
                if($biaos !=3){
                    $biaos = 2;
                }
            }
            if($biaos == 1 || $biaos ==2 || $biaos == 3){
                $encu = Db::name('enterprise_custom')->where('enterprise_id','=',$enterId)->where('custom_id','=',$custom['id'])->find();
                if($encu){
                    $biaos = 4;
                }
            }
        }
        $commonuser = new Commonuser();

        if($biaos == 0){
            $data['name'] = $name;
            $data['phone'] = $phone;
            $data['identityNo'] = $identityNo;
            $customId = $commonuser->operatecustom($data);
        }else if($biaos == 1){
            $customId = $custom['id'];
        }else if($biaos == 2){
            $data['name'] = $name;
            $data['phone'] = $phone;
            $data['identityNo'] = $identityNo;
            $customId = $commonuser->operatecustom($data,$custom['id']);
        }else if($biaos ==3){
            return 300;//信息不正确
        }else if($biaos == 4){
            return 301;//成员已存在
        }
        $ecdata['custom_id'] = $customId;
        $ecdata['enterprise_id'] = $enterId;
        $ecdata['purview'] = 2;
        $ecdata['createtime'] = time();
        $encuId = Db::name('enterprise_custom')->insertGetId($ecdata);
        if($custom){
            if($custom['attestation'] == 1){
                $this->addmember($encuId);
            }
        }
        return 200;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月21月 11:20:38
     * ps:添加成员
     */
    public function addmember($encuId){
        $encu = Db::name('enterprise_custom')->where('id','=',$encuId)->find();
        $enter = Db::name('enterprise')->where('id','=',$encu['enterprise_id'])->find();
        $custom = Db::name('custom')->where('id','=',$encu['custom_id'])->find();
        $fadada = new Fadada();
        //企业已认证
        if($enter['attestation'] == 1){
            $data[0]['memberName'] = $custom['name'];
            $data[0]['internalIdentifier'] = $custom['identityNo'];
            $data[0]['memberMobile'] = $custom['phone'];
            $res = $fadada->createmember($enter['account'],$data);

            if($res['code'] == 200){
                $edit['memberId'] = $res['memberId'];
                $edit['fadada'] = 1;

                $edit['updatetime'] = time();
                Db::name('enterprise_custom')->where('id','=',$encuId)->update($edit);
            }
        }
        return true;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月21月 15:04:16
     * ps:删除企业成员
     */
    public function delmember($encuId){
        $encu = Db::name('enterprise_custom')->where('id','=',$encuId)->find();
        $enter = Db::name('enterprise')->where('id','=',$encu['enterprise_id'])->find();
        $fadada = new Fadada();
        //企业已认证

        if($encu['memberId']){
            $data = [$encu['memberId']];
            $res = $fadada->deletemember($enter['account'],$data);

        }
        Db::name('enterprise_custom')->where('id','=',$encuId)->delete();
        //删除印章授权
        Db::name('enterprise_custom_signature')->where('encu_id','=',$encuId)->delete();
        return true;
    }
}
