<?php

namespace app\common\controller;


use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use think\Controller;
use think\Db;


/**
 * 印章公共接口
 */
class Commonsignature extends Controller
{


    public function _initialize()
    {


        parent::_initialize();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 15:55:56
     * ps:添加印章
     */
   public function addsignature($data,$type,$typeId){
        $data['type'] = $type;
        $data['type_id'] = $typeId;
        $data['createtime'] = time();
        $ids = Db::name('signature')->insertGetId($data);
        return $ids;
   }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 15:55:56
     * ps:修改印章
     */
    public function editsignature($data,$ids){
        $data['updatetime'] = time();

        $ids = Db::name('signature')->where('id','=',$ids)->update($data);
        return $ids;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 15:55:56
     * ps:查询平台印章信息并同步信息
     */
    public function getplatformsignature($ids){
        $signature = Db::name('signature')->where('id','=',$ids)->find();
        if($signature['type'] == 'custom'){
            //个人
            $cusotm = Db::name('custom')->where('id','=',$signature['type_id'])->find();
            $account = $cusotm['account'];
            $type = 2;
        }else{
            //企业
            $enter = Db::name('enterprise')->where('id','=',$signature['type_id'])->find();
            $account = $enter['account'];
            $type = 1;

        }

        $fadada = new Fadada();
        $res = $fadada->getseals($account,$signature['sealNo'],$type);
        if($res['code'] == 200){
            if(isset($res['sealName'])){
                $data['name'] = $res['sealName'];
                $data['default'] = $res['isDefault'];
                $data['img'] = $this->xiazsignature($res['sealUrl'],$signature['sealNo']);
                $data['updatetime'] = time();
                Db::name('signature')->where('id','=',$ids)->update($data);
                if($res['isDefault'] == 1){
                    //其他印章改为不是默认
                    $edit['default'] = 0;
                    $edit['updatetime']=time();
                    Db::name('signature')->where('id','<>',$ids)->where('type','=',$signature['type'])->where('type_id','=',$signature['type_id'])->update($edit);
                }
            }

        }

        $rest['code'] = 200;
        return $rest;

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 17:08:49
     * ps:下载印章图片
     */
    public function xiazsignature($imageUrl,$sealNo){


        // 要保存图片的本地路径和文件名
        $localPath = 'signature/'.$sealNo.'.png';

        // 尝试获取图片内容
        $imageContent = file_get_contents($imageUrl);

        if ($imageContent !== false) {
            // 尝试将图片内容写入到本地文件
            if (file_put_contents($localPath, $imageContent)) {
               return  request()->domain().'/'.$localPath;

            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 15:55:56
     * ps:添加平台印章
     */
    public function addplatformsignature($typeId,$type,$url=''){
        if($type == 'custom'){
            $custom = Db::name('custom')->where('id','=',$typeId)->find();
            $account = $custom['identityNo'];
            $user = '';
//            $sealNo = '';
        }else{
            //企业
            $enter = Db::name('enterprise')->where('id','=',$typeId)->find();
            $account = $enter['account'];
            //企业绑定下的人
            $encutom = Db::name('enterprise_custom as e')
                ->join('custom','custom.id = e.custom_id')
                ->where('custom.attestation','=',1)
                ->where('enterprise_id','=',$typeId)

                ->find();

            $user = $encutom['identityNo'];
//            $sealNo = $this->generateRandomString();
        }
//        $lovesigning = new Lovesigning();
        $fadata = new Fadada();
        $res = $fadata->addseals($account,$type,$user,$url);

        $rest['code'] = 300;
        $rest['msg'] = '未知错误，请稍后重试！';

        if($res['code'] == 200){
            //创建印章
           // $rest['bizImgNo'] = $res['bizImgNo'];//制作印章返回信息
            $rest['hwBoardUrl'] = $res['hwBoardUrl'];//在线制作印章链接（有效期3小时）
            $rest['code'] = 200;
        }else{
            $rest['msg'] = $res['msg'];
        }

        return $rest;
    }
    //生成印章编号
    function generateRandomString($length = 32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 9:09:23
     * ps:设置默认签章
     */
    public function setdefault($ids){
        $signature = Db::name('signature')->where('id','=',$ids)->find();
        if($signature['type'] == 'custom'){
            //个人
            $cusotm = Db::name('custom')->where('id','=',$signature['type_id'])->find();
            $account = $cusotm['account'];
        }else{
            //企业
            $enter = Db::name('enterprise')->where('id','=',$signature['type_id'])->find();
            $account = $enter['account'];
        }
       // $lovesigning = new Lovesigning();
//        $res = $lovesigning->setDefaultSeal($account,$signature['sealNo']);
//        if($res['code'] == 200){
            //$this->getplatformsignature($ids);
//        }
        $data['default'] = 1;

        $data['updatetime'] = time();
        Db::name('signature')->where('id','=',$ids)->update($data);

            //其他印章改为不是默认
            $edit['default'] = 0;
            $edit['updatetime']=time();
            Db::name('signature')->where('id','<>',$ids)->where('type','=',$signature['type'])->where('type_id','=',$signature['type_id'])->update($edit);

        return true;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 9:09:23
     * ps:删除印章
     */
    public function delsignature($ids){
        $signature = Db::name('signature')->where('id','=',$ids)->find();
        if($signature['type'] == 'custom'){
            //个人
            $cusotm = Db::name('custom')->where('id','=',$signature['type_id'])->find();
            $account = $cusotm['account'];
            $type = 2;
        }else{
            //企业
            $enter = Db::name('enterprise')->where('id','=',$signature['type_id'])->find();
            $account = $enter['account'];
            $type = 1;
        }
//        $lovesigning = new Lovesigning();
        $fadada = new Fadada();
        $res = $fadada->removeSeal($account,$signature['sealNo'],$type);
        if($res['code'] == 200){
            $data['deletetime'] = time();
            Db::name('signature')->where('id','=',$ids)->update($data);
        }
        return $res;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月12月 17:15:47
     * ps:获取数字签名证书
     */
    public function getcertinfo($type,$typeId){

        if($type == 'custom'){

            //个人
            $cusotm = Db::name('custom')->where('id','=',$typeId)->find();
            $account = $cusotm['account'];
            $usertype = 2;
        }else{
            //企业
            $enter = Db::name('enterprise')->where('id','=',$typeId)->find();
            $account = $enter['account'];
            $usertype = 1;
        }

        $fadada = new Fadada();
        $res = $fadada->getcertinfo($account,$usertype);

        if($res['code'] == 200){
            foreach($res['list'] as $k=>$v){
                $data['type'] = $type;
                $data['type_id'] =$typeId;
                $data['certNo'] = $v['certNo'];
                $data['ownerName'] = $v['ownerName'];
                $data['certCA'] = $v['certCA'];
                $data['encryptionType'] = $v['encryptionType'];
                $data['validPeriod'] = $v['validPeriod'];
                $data['status'] = $v['status'];
                $data['certImg'] =$this->xiazsignature($v['certImg'],$v['certNo']);;
                $data['createtime'] = time();
                Db::name('cert')->insertGetId($data);
                $url = $data['certImg'];
            }

        }
        return $url;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月12月 10:27:59
     * ps:印章授权
     */
    public function sealauthorize($enterId,$customId,$encustomId,$sealId,$starttime=null,$endtime=null,$url=''){
        $enter = Db::name('enterprise')->where('id','=',$enterId)->find();
        $custom = Db::name('custom')->where('id','=',$customId)->find();

        $encustom = Db::name('enterprise_custom as e')
            ->join('custom','e.custom_id = custom.id')
            ->where('e.id','in',$encustomId)
            ->find();

        $seal = Db::name('signature')->where('id','=',$sealId)->find();

//        foreach($encustom as $k=>$v){
//            $memberIds = [$v['account']];
//        }
        $memberInfo['memberIds'] = [$encustom['memberId']];
        $memberInfo['grantStartTime'] = $starttime;
        $memberInfo['grantEndTime'] = $endtime;

        $fadada = new Fadada();
        $res = $fadada->grantgeturl($enter['account'],$seal['sealNo'],$memberInfo,$custom['identityNo'],$url);

        return $res;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月14月 13:38:55
     * ps:获取成员印章授权列表
     */
    public function getsealauthorize($ids){
        $encusign = Db::name('enterprise_custom_signature')
            ->where('id','=',$ids)
            ->find();
        $encu = Db::name('enterprise_custom as e')
            ->join('enterprise','enterprise.id = e.enterprise_id')
            ->where('e.id','=',$encusign['encu_id'])
            ->find();
        $signature = Db::name('signature')->where('id','=',$encusign['signature_id'])->find();
        $fadada = new Fadada();
        $res = $fadada->getsealauthorize($encu['account'],$encu['memberId']);
        if($res['code']==200){
            if($res['data']['sealInfos']){
                foreach($res['data']['sealInfos'] as $k=>$v){
                    if($v['sealId'] == $signature['sealNo']){
                        $edit['updatetime'] = time();
                        if($v['grantStartTime']){
                            $edit['starttime'] = $v['grantStartTime']/1000;
                        }
                        if($v['grantEndTime']){
                            $edit['endtime'] = $v['grantEndTime']/1000;
                        }
                        Db::name('enterprise_custom_signature')
                            ->where('encu_id','=',$ids)
                            ->update($edit);
                    }
                }
            }
        }
        return true;
    }
}
