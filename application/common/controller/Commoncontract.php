<?php

namespace app\common\controller;


use app\api\controller\Csms;
use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use think\Controller;
use think\Db;


/**
 * 合同公共方法
 */
class Commoncontract extends Controller
{


    public function _initialize()
    {


        parent::_initialize();
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 15:35:33
     * ps:发起签约
     */
//    public function initiatesigning($account,$type,$typeId){
//        $contractNo = $this->addcontractNo();
//        $lovesigning = new Lovesigning();
//        $res = $lovesigning->initiateUrl($contractNo,$account);
//        if($res['code'] == 200){
//            $data['initiateType'] = $type;
//            $data['initiate_id'] = $typeId;
//            $data['contractNo'] = $contractNo;
//            $data['url'] = $res['initiateUrl'];
//            $data['state'] = 10;
//
//            $this->operatecontract($data,$type,$typeId);
//        }
//        return $res;
//    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 15:30:17
     * ps:生成合同编号
     */
    public function addcontractNo($platformId=0){
        $platform = Db::name('platform_setup')->where('enterprise_id','=',$platformId)->find();
        $No1 = $platform['contractNo1'];
        $No2 = '';
        $No3 = '';
        $No4 = '';
        switch ($platform['contractNo2']){
            case 0:
                $No2 = date('Ymd',time());
                break;
            case 1:
                $No2 = date('Ym',time());
                break;
            case 2:
                $No2 = date('Y',time());
                break;
        }
        switch ($platform['contractNo3']){
            case 1:
                $No3 = random_int(1, 9);
                break;
            case 2:
                $No3 = random_int(10, 99);
                break;
            case 3:
                $No3 = random_int(100, 999);
                break;
            case 4:
                $No3 = random_int(1000, 9999);
                break;
            case 5:
                $No3 = random_int(10000, 99999);
                break;
            case 6:
                $No3 = random_int(100000, 999999);
                break;
            case 7:
                $No3 = random_int(1000000, 9999999);
                break;
            case 8:
                $No3 = random_int(10000000, 99999999);
                break;
            case 9:
                $No3 = random_int(100000000, 999999999);
                break;
            case 10:
                $No3 = random_int(1000000000, 9999999999);
                break;
        }
        if($platform['contractNo4'] == 1){
            $No = $No1.'-'.$No2.'-'.$No3;
        }else{
            $No = $No1.$No2.$No3;
        }
        $con = Db::name('contract')->where('contractNo','=',$No)->field('id')->find();
        if($con){
            $this->addcontractNo($platformId);
        }else{
            return $No; // 输出类似 CT-123e4567-e89b-12d3-a456-426614174000
        }
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 11:46:23
     * ps:操作合同信息
     */
    public function operatecontract($data,$type,$typeId,$ids=null){

        $data['initiateType'] = $type;
        $data['initiate_id'] = $typeId;
        if($ids){
            $data['updatetime'] = time();
            Db::name('contract')->where('id','=',$ids)->update($data);
        }else{
            $data['createtime'] = time();
            $ids = Db::name('contract')->insertGetId($data);

        }

        return $ids;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 11:56:31
     * ps:获取接口合同信息并操作
     * $query:合同id，合同编号
     * $type:合同查询类型（1：合同id查询；2：合同编号查询）
     */
    public function getapicontract($query,$type,$usertype=null,$typeId=null){
        if($type == 1){
            $contract = Db::name('contract')->where('id','=',$query)->find();
            $contractNo = $contract['taskId'];
            $usertype = $contract['initiateType'];
            $typeId = $contract['initiate_id'];
        }else{
            $contractNo = $query;
        }
        $fadada = new Fadada();
        $res = $fadada->getcontract($contractNo);
        //dump($res);exit;
        if($res['code'] == 200){
            //合同号查询后创建合同信息并添加相应的信息

//            $data['contractNo'] = $contractNo;
            $data['contractName'] = $res['contractName'];
           // $data['expireTime'] =$res['expireTime'];
            $data['state'] =$this->getstate($res['status'])['state'];
            if($type ==2){

                $data['template'] =0;
//                $data['shortUrl'] =$res['shortUrl'];
                $data['initiateType'] = $usertype;
                $data['initiate_id'] = $typeId;

                $contract = Db::name('contract')->where('contractNo','=',$contractNo)->find();
                if($contract){
                    $contractId = $this->operatecontract($data,$usertype,$typeId,$contract['id']);

                }else{
                    $contractId = $this->operatecontract($data,$usertype,$typeId);

                }

                //合同添加完后添加合同签署方
                foreach($res['signing'] as $k=>$v){
                    $sigdata['contract_id'] = $contractId;
                    $sigdata['signUrl'] = $v['url'];
//                    $sigdata['signOrder'] = $v['signOrder'];
//                    $sigdata['signType'] = $v['signType'];
//                    $sigdata['validateType'] = $v['validateType'];
                    $sigdata['createtime'] = time();
                    $sigdata['account'] = $v['account'];
                    if($v['userType'] == 1){
                        $enter = Db::name('enterprise')->where('account','=',$v['account'])->find();
                        if($enter){
                            //用户存在
                            $sigdata['type'] = 'enterprise';
                            $sigdata['type_id'] = $enter['id'];
                        }else{
                            //用户不存在
                            //先添加信息
                            $endata['name'] = $v['enterName'];
                            $endata['legalName'] = $v['name'];
                            $endata['legalNo'] = $v['identityNo'];
                            $endata['legalPhone'] = $v['phone'];
                            $endata['account'] = $v['account'];

                            $endata['createtime'] = time();
                            $commonenter = new Commonenter();
                            $enterId = $commonenter->operateenter($endata);
//                            $resuser = $lovesigning->getuser($v['account'],1,1);
                            $resuser = $fadada->getuser($v['account'],1,1);
                            if($resuser['code'] == 200){
                                //获取企业详细信息并修改
                                $resuserdata['account'] = $resuser['account'];
                                $resuserdata['name'] = $resuser['name'];
                                $resuserdata['proveNo'] = $resuser['identityNo'];
                                $resuserdata['phone'] = $resuser['phone'];
                                $resuserdata['attestation'] = $resuser['attestation'];//认证状态（0：未认证；1：已认证)
                                $resuserdata['attestationType'] = $resuser['attestationType'];
                                $resuserdata['finishedTime'] = $resuser['finishedTime'];
                                $resuserdata['serialNo'] = $resuser['serialNo'];
                                $commonenter->operateenter($resuserdata,$enterId);
                            }
                            //获取用户信息并修改
                            if($v['identityNo']){
                                $custom = Db::name('custom')->where('identityNo','=',$v['identityNo'])->find();
                            }else{
                                $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                            }

//                            $rescustom = $lovesigning->getuser($custom['identityNo'],3,2);
                            $rescustom = $fadada->getuser($custom['identityNo'],3,2);

                            if($rescustom['code'] == 200){
                                $commonuser = new Commonuser();
                                //获取个人信息并修改
                                $rescustomdata['account'] = $rescustom['account'];
                                $rescustomdata['name'] = $rescustom['name'];
                                $rescustomdata['identityNo'] = $rescustom['identityNo'];
                                $rescustomdata['phone'] = $rescustom['phone'];
                                $rescustomdata['attestation'] = $rescustom['attestation'];//认证状态（0：未认证；1：已认证)
                                $rescustomdata['attestationType'] = $rescustom['attestationType'];
                                $rescustomdata['finishedTime'] = $rescustom['finishedTime'];
                                $rescustomdata['serialNo'] = $rescustom['serialNo'];
                                $commonuser->operatecustom($rescustomdata,$custom['id']);
                            }

                            $sigdata['type'] = 'enterprise';
                            $sigdata['type_id'] = $enterId;
                        }
                    }else{
                        $custom = Db::name('custom')->where('account','=',$v['account'])->find();
                        if($custom){
                            //用户存在
                            $sigdata['type'] = 'custom';
                            $sigdata['type_id'] = $custom['id'];
                        }else{
                            //用户不存在
                            //$rescustom = $lovesigning->getuser($v['account'],1,2);
                            $rescustom = $fadada->getuser($v['account'],1,2);

                            $commonuser = new Commonuser();

                            if($rescustom['code'] == 200){
                                //获取个人信息并修改
                                $rescustomdata['account'] = $rescustom['account'];
                                $rescustomdata['name'] = $rescustom['name'];
                                $rescustomdata['identityNo'] = $rescustom['identityNo'];
                                $rescustomdata['phone'] = $rescustom['phone'];
                                $rescustomdata['attestation'] = $rescustom['attestation'];//认证状态（0：未认证；1：已认证)
                                $rescustomdata['attestationType'] = $rescustom['attestationType'];
                                $rescustomdata['finishedTime'] = $rescustom['finishedTime'];
                                $rescustomdata['serialNo'] = $rescustom['serialNo'];
                                $customId = $commonuser->operatecustom($rescustomdata);

                            }else{
                                //未获取到信息直接添加
                                $rescustomdata['account'] = $v['account'];
                                $rescustomdata['name'] = $v['name'];
                                $rescustomdata['identityNo'] = $v['identityNo'];
                                $rescustomdata['phone'] = $v['phone'];
                                $customId = $commonuser->operatecustom($rescustomdata);
                            }
                            $sigdata['type'] = 'custom';
                            $sigdata['type_id'] = $customId;
                        }
                    }

                    $this->operatesigning($contractId,$sigdata);
                }
            }else{
                //通过合同id查询
                $contractId = $this->operatecontract($data,$usertype,$typeId,$query);
                //修改签约用户认证状态
                //合同添加完后添加合同签署方
                $commonenter = new Commonenter();


                if(isset($res['signing'])){
                    foreach($res['signing'] as $k=>$v){
                        $cz = 0;
                        if($v['userType'] == 1){
                            //企业
                            $enter = Db::name('enterprise')->where('account','=',$v['account'])->find();
                            if($enter){
                                $cz = 1;
                                $type_id = $enter['id'];
                                $type = 'enterprise';
                                if($enter['attestation'] == 0){
                                    $resuser = $fadada->getuser($v['account'],1,1);

//                                $resuser = $lovesigning->getuser($v['account'],1,1);
                                    if($resuser['code'] == 200){
                                        //获取企业详细信息并修改
                                        if(!$enter['legalNo']){
                                            $resuserdata['legalNo'] = $resuser['identityNo'];//认证状态（0：未认证；1：已认证)
                                        }

                                        $resuserdata['attestation'] = $resuser['attestation'];//认证状态（0：未认证；1：已认证)
                                        $resuserdata['attestationType'] = $resuser['attestationType'];
                                        $resuserdata['finishedTime'] = $resuser['finishedTime'];
                                        $resuserdata['serialNo'] = $resuser['serialNo'];
                                        $commonenter->operateenter($resuserdata,$enter['id']);
                                    }

                                    if($v['identityNo']){
                                        //获取用户信息并修改
                                        $custom = Db::name('custom')->where('identityNo','=',$v['identityNo'])->find();
                                    }else{
                                        //获取用户信息并修改
                                        $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                                    }

                                    //$rescustom = $lovesigning->getuser($custom['account'],1,2);
                                    $rescustom = $fadada->getuser($custom['account'],1,2);

                                    if($rescustom['code'] == 200){
                                        $commonuser = new Commonuser();
                                        //获取个人信息并修改
                                        $rescustomdata['attestation'] = $rescustom['attestation'];//认证状态（0：未认证；1：已认证)
                                        $rescustomdata['attestationType'] = $rescustom['attestationType'];
                                        $rescustomdata['finishedTime'] = $rescustom['finishedTime'];
                                        $rescustomdata['serialNo'] = $rescustom['serialNo'];
                                        $commonuser->operatecustom($rescustomdata,$custom['id']);
                                    }
                                }
                            }


                        }else{
                            //个人
                            //获取用户信息并修改
                            $custom = Db::name('custom')->where('account','=',$v['account'])->find();
//                            $rescustom = $lovesigning->getuser($custom['account'],1,2);
                            if($custom){
                                $cz = 1;
                                $rescustom = $fadada->getuser($custom['account'],1,2);
                                $type_id = $custom['id'];
                                $type = 'custom';

                                if($rescustom['code'] == 200){
                                    $commonuser = new Commonuser();
                                    //获取个人信息并修改
                                    $rescustomdata['attestation'] = $rescustom['attestation'];//认证状态（0：未认证；1：已认证)
                                    $rescustomdata['attestationType'] = $rescustom['attestationType'];
                                    $rescustomdata['finishedTime'] = $rescustom['finishedTime'];
                                    $rescustomdata['serialNo'] = $rescustom['serialNo'];
                                    $commonuser->operatecustom($rescustomdata,$custom['id']);
                                }
                            }

                        }
                        if($cz == 1){
                            $signing = Db::name('contract_signing')
                                ->where('contract_id','=',$contractId)
                                ->where('type','=',$type)
                                ->where('type_id','=',$type_id)
                                ->find();
                            if($v['state'] == 'wait_sign'){
                                $singdata['state'] = 0;

                            }else if($v['state'] == 'signed'){
                                $singdata['state'] = 1;

                            }else if($v['state'] == 'sign_rejected'){
                                $singdata['state'] = 2;

                            }
                            $singdata['account'] = $v['account'];
                            $this->operatesigning($contractId,$singdata,$signing['id']);
                        }

                    }
                }

            }
        }
        return true;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月18月 16:18:19
     * ps:合同状态匹配
     */
    public function getstate($state){
        switch ($state){
            case 'task_created':
                $res['state'] = 0;
                $res['name'] = '待签约';
                break;
            case 'finish_creation':
                $res['state'] = 0;
                $res['name'] = '待签约';
                break;
            case 'fill_progress':
                $res['state'] = 0;
                $res['name'] = '待签约';
                break;
            case 'fill_completed':
                $res['state'] = 0;
                $res['name'] = '待签约';
                break;
            case 'sign_progress':
                $res['state'] = 1;
                $res['name'] = '签约中';
                break;
            case 'sign_completed':
                $res['state'] = 2;
                $res['name'] = '已签约';
                break;
            case 'task_finished':
                $res['state'] = 2;
                $res['name'] = '已签约';
                break;
            case 'task_terminated':
                $res['state'] = 4;
                $res['name'] = '拒签';
                break;
            case 'expired':
                $res['state'] = 3;
                $res['name'] = '过期';
                break;
            case 'abolishing':
                $res['state'] = 6;
                $res['name'] = '作废';
                break;
            case 'revoked':
                $res['state'] = 6;
                $res['name'] = '作废';
                break;
        }
        return $res;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 11:46:23
     * ps:操作合同签署人信息
     */
    public function operatesigning($contractId,$data,$ids= null){
        $sign = Db::name('contract_signing')->where('contract_id','=',$contractId)->where('account','=',$data['account'])->find();
        $data['contract_id'] = $contractId;
        if($ids){
            $data['updatetime'] = time();
            Db::name('contract_signing')->where('id','=',$ids)->update($data);
        }else{
            if($sign){
                $data['updatetime'] = time();
                Db::name('contract_signing')->where('id','=',$sign['id'])->update($data);
            }else{
                $data['createtime'] = time();
                $ids = Db::name('contract_signing')->insertGetId($data);

            }

        }


        return $ids;
    }



    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 10:03:08
     * ps:下载合同
     */
    public function download($ids){
        $contract = Db::name('contract')->where('id','=',$ids)->find();

        $fadada = new Fadada();
        $res = $fadada->downloadContract($contract['taskId']);
        $url = '';
        if($res['code'] == 200){
            $url = $this->downloadPdfFromUrl($res['url'],$contract['contractNo']);
            dump($url);exit;
            $data['contractFile']  = $url;
            $this->operatecontract($data,$contract['initiateType'],$contract['initiate_id'],$contract['id']);
        }
        return $url;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 17:08:49
     * ps:下载合同文件
     */
    public function xiazcontract($base64String,$name){
// 解码Base64字符串

//        $binaryData = base64_decode($base64String);
        $binaryData =$base64String;

// 指定保存的文件名和路径
        $savePath = "contract/".$name.".pdf"; // 请替换为实际的路径和文件名

// 确保目录存在
        if (!file_exists(dirname($savePath))) {
            mkdir(dirname($savePath), 0777, true); // 创建目录，根据需要调整权限
        }

// 写入文件
        if (file_put_contents($savePath, $binaryData)) {

            return  request()->domain().'/'.$savePath;

        } else {
           return '';
        }
    }
    function downloadPdfFromUrl($url, $name) {

        $saveTo = "contract/".$name; // 请替换为实际的路径和文件名
        // 初始化cURL会话
        $ch = curl_init();

        // 设置cURL选项
        curl_setopt($ch, CURLOPT_URL, $url); // 目标URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 设置为1表示将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取到的数据以原生的形式返回。
        curl_setopt($ch, CURLOPT_HEADER, 0); // 启用时会将头文件的信息作为数据流输出。

        // 执行cURL会话
        $file = curl_exec($ch);

        // 关闭cURL资源，并且释放系统资源
        curl_close($ch);

        // 尝试将文件写入到本地
        if (file_put_contents($saveTo, $file)) {
            echo "PDF文件下载成功，并保存到: $saveTo";
        } else {
            echo "文件写入失败，请检查权限或路径是否正确。";
        }
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月07月 15:52:14
     * ps:发起合同(模版)
     */
    public function initiatecontract($contractId){
        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        $template = Db::name('template')->where('id','=',$contract['template_id'])->find();
        $cotecontent = Db::name('contract_template_content')->where('contract_id','=',$contract['id'])->select();
        $openId = '';
        $idType = '';
        if($contract['initiateType'] == 'enterprise'){
            $idType='corp';
            $enter = Db::name('enterprise')->where('id','=',$contract['initiate_id'])->field('account')->find();
            $openId = $enter['account'];
        }else{
            $idType='person';
            $custom = Db::name('custom')->where('id','=',$contract['initiate_id'])->field('account')->find();
            $openId = $custom['account'];

        }
//        $lovesigning = new Lovesigning();
        $fadada = new Fadada();
        $signing = Db::name('contract_signing')->where('contract_id','=',$contract['id'])->select();

        foreach($signing as $k=>$v){
            $actors[$k]['actor']['actorId'] = $v['TCN'];
            if($v['type'] == 'enterprise'){
                $actors[$k]['actor']['actorType'] = 'corp';
                $enter = Db::name('enterprise')->where('id','=',$v['type_id'])->find();
                $actors[$k]['actor']['actorName'] = $enter['name'];
                if($enter['account']){
                    $actors[$k]['actor']['actorOpenId'] = $enter['account'];
                }
            }else{
                $actors[$k]['actor']['actorType'] = 'person';
                $custom = Db::name('custom')->where('id','=',$v['type_id'])->find();
                $actors[$k]['actor']['actorName'] = $custom['name'];

                if($custom['account']){
                    $actors[$k]['actor']['actorOpenId'] = $custom['account'];
                }
            }

        }


        $res = $fadada->createContract($contract['contractNo'],$contract['contractName'],$contract['expireTime'],$template['templateNo'],$openId,$idType,$contract['signingTime'],$actors);
        $res['url'] = '';

        if($res['code'] == 200){
//            $data['contractFile'] = $res['contractFile'];
            $data['taskId'] = $res['signTaskId'];

            $this->operatecontract($data,$contract['initiateType'],$contract['initiate_id'],$contract['id']);

            //添加合同模版内容
            //查询模板文件id
            if($template['docId']){
                $docId = $template['docId'];
            }else{
               $taskdetail = $fadada->getcontenttaskdetail($res['signTaskId']);
               if($taskdetail['code']==200){
                   $docId = $taskdetail['docId'];
               }
            }
            $fillData = array();
            foreach($cotecontent as $k=>$v){
                $fillData[$k]['docId'] =$docId;
                $fillData[$k]['fieldName'] = $v['name'];
                $fillData[$k]['fieldValue'] = $v['content'];
            }
            //添加模版内容
            $fillrest = $fadada->fillvalues($res['signTaskId'],$fillData);

            //上传合同附件
            $annex = Db::name('contract_annex')->where('contract_id','=',$contractId)->select();
            $annexdata = array();
            $addattach = array();
            foreach($annex as $k=>$v){
                $annexfileres = $fadada->uploadbyurl('attach',$v['file']);

                if($annexfileres['code'] == 200){
                    $annexedit['fileapiurl'] = $annexfileres['url'];
                    $process = $fadada->process('attach',$annexfileres['url'],$v['name']);

                    if($process['code'] == 200){
                        $annexedit['fileId'] = $process['fileId'];
                        $annexdata[$k]['attachId'] = $this->createdocId();
                        $annexdata[$k]['attachName'] = $v['name'];
                        $annexdata[$k]['attachFileId'] = $process['fileId'];
                        Db::name('contract_annex')->where('id','=',$v['id'])->update($annexedit);

                        $addattach[$k]['attachId'] = $annexdata[$k]['attachId'];
                        $addattach[$k]['attachName'] = $v['name'];
                        $addattach[$k]['attachFileId'] = $process['fileId'];

                    }

                }
            }

            //法大大上传附件
            $fadada->addattach($res['signTaskId'],$addattach);
            $resurl = $this->getapicontracturl($contractId);
            if($resurl['code'] == 200){
                $res['url'] = $resurl['url'];
            }
//            if($fillrest['code'] == 200){
//                $fadada->startfill($res['signTaskId']);
//            }
        }
        return $res;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月19月 11:06:59
     * ps:发起合同(上传文件)
     */
    public function initiatecontractfile($contractId){
        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        $fadada = new Fadada();
        if(!$contract['taskId']){
            //上传合同文件
            $fileres = $fadada->uploadbyurl('doc',$contract['contractFile']);

            if($fileres['code'] == 200){
                $edit['fileapiurl'] = $fileres['url'];
                $process = $fadada->process('doc',$fileres['url'],$contract['fileName']);

                if($process['code'] == 200){
                    $edit['fileapiId'] = $process['fileId'];
                    //创建文件id（随机生成）
                    $docId = $this->createdocId();
                    $docs['docId'] = $docId;
                    $docs['docName'] = $contract['fileName'];
                    $docs['docFileId'] = $process['fileId'];
                    $signing = Db::name('contract_signing')->where('contract_id','=',$contract['id'])->select();
                    $data = array();
                    foreach($signing as $k=>$v){
                        $data[$k]['actor']['actorId'] = $v['TCN'];
                        if($v['type'] == 'enterprise'){
                            $data[$k]['actor']['actorType'] = 'corp';
                            $enter = Db::name('enterprise')->where('id','=',$v['type_id'])->find();
                            $data[$k]['actor']['actorName'] = $enter['name'];
                            if($enter['account']){
                                $data[$k]['actor']['actorOpenId'] = $enter['account'];
                            }
                        }else{
                            $data[$k]['actor']['actorType'] = 'person';
                            $custom = Db::name('custom')->where('id','=',$v['type_id'])->find();
                            $data[$k]['actor']['actorName'] = $custom['name'];

                            if($custom['account']){
                                $data[$k]['actor']['actorOpenId'] = $custom['account'];
                            }
                        }
                        $data[$k]['actor']['permissions'] = ['sign'];

                        $data[$k]['signConfigInfo']['verifyMethods'] = ['sms'];
                    }
                    //上传合同附件
                    $annex = Db::name('contract_annex')->where('contract_id','=',$contractId)->select();
                    $annexdata = array();
                    foreach($annex as $k=>$v){
                        $annexfileres = $fadada->uploadbyurl('attach',$v['file']);

                        if($annexfileres['code'] == 200){
                            $annexedit['fileapiurl'] = $annexfileres['url'];
                            $process = $fadada->process('attach',$annexfileres['url'],$v['name']);

                            if($process['code'] == 200){
                                $annexedit['fileId'] = $process['fileId'];
                                $annexdata[$k]['attachId'] = $this->createdocId();
                                $annexdata[$k]['attachName'] = $v['name'];
                                $annexdata[$k]['attachFileId'] = $process['fileId'];
                                Db::name('contract_annex')->where('id','=',$v['id'])->update($annexedit);
                            }

                        }
                    }
                }else{
                    $return['msg'] = $process['msg'];
                    $return['code'] = 300;
                    return $return;
                }
            }else{
                $return['msg'] = $fileres['msg'];
                $return['code'] = 300;
                return $return;
            }
        }else{
            $res['code'] = 200;
            $res['signTaskId'] = $contract['taskId'];
        }
        $openId = '';
        $idType = '';
        if($contract['initiateType'] == 'enterprise'){
            $idType='corp';
            $enter = Db::name('enterprise')->where('id','=',$contract['initiate_id'])->field('account')->find();
            $openId = $enter['account'];
        }else{
            $idType='person';
            $custom = Db::name('custom')->where('id','=',$contract['initiate_id'])->field('account')->find();
            $openId = $custom['account'];

        }
        $res = $fadada->createContractfile($contract['contractNo'],$contract['contractName'],$contract['expireTime'],$docs,$data,$annexdata,$openId,$idType,$contract['signingTime']);
        $return['url'] = '';
        if($res['code']==200){
            $edit['taskId'] = $res['signTaskId'];
            $this->operatecontract($edit,$contract['initiateType'],$contract['initiate_id'],$contract['id']);
            //$fadada->startfill($res['signTaskId']);
            $resurl = $this->getapicontracturl($contractId);
            if($resurl['code'] == 200){
                $return['url'] = $resurl['url'];
            }
            $return['code'] = 200;
        }else{
            $return['msg'] = $res['msg'];
            $return['code'] = 300;
        }


        return $return;
    }

    //生成docId
    public function createdocId(){

        $randomNumber = '';
        for ($i = 0; $i < 8; $i++) {
            // rand(0, 9) 生成一个0到9之间的随机数
            $randomNumber .= rand(0, 9);
        }

        return $randomNumber;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月18月 15:01:16
     * ps:获取签署方签署链接
     */
    public function getsignerurl($signingId,$redirectUrl=''){
        $signing = Db::name('contract_signing')->where('id','=',$signingId)->find();
        $contract = Db::name('contract')->where('id','=',$signing['contract_id'])->find();
        if($signing['type'] == 'enterprise'){
            $enter = Db::name('enterprise')->where('id','=',$signing['type_id'])->find();
            $custom = Db::name('custom')->where('id','=',$signing['custom_id'])->find();
            $no = $custom['identityNo'];
        }else{
            $custom = Db::name('custom')->where('id','=',$signing['type_id'])->find();
            $no = $custom['identityNo'];
        }
        $fadada = new Fadada();
        $res = $fadada->getactorurl($contract['taskId'],$signing['TCN'],$no,$redirectUrl);
        $rest['code'] = 300;
        $rest['url'] = '';
        $rest['msg'] = '';
        if($res['code'] == 200){
            $rest['code'] = 200;
            $rest['url'] = $res['url'];
        }else{
            $rest['msg'] = $res['msg'];
        }
        return $rest;

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月07月 22:28:53
     * ps:添加签约平台，合同签署方
     */
    public function addSigner($contractId){
        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        $signing = Db::name('contract_signing')->where('contract_id','=',$contract['id'])->select();
        $template = Db::name('template')->where('id','=',$contract['template_id'])->find();
        $data = array();
        foreach($signing as $k=>$v){

            $data[$k]['actor']['actorId'] = $v['TCN'];
            if($v['type'] == 'enterprise'){
                $data[$k]['actor']['actorType'] = 'corp';
                $enter = Db::name('enterprise')->where('id','=',$v['type_id'])->find();
                $data[$k]['actor']['actorName'] = $enter['name'];
                if($enter['account']){
                    $data[$k]['actor']['actorOpenId'] = $enter['account'];
                }
            }else{
                $data[$k]['actor']['actorType'] = 'person';
                $custom = Db::name('custom')->where('id','=',$v['type_id'])->find();
                $data[$k]['actor']['actorName'] = $custom['name'];

                if($custom['account']){
                    $data[$k]['actor']['actorOpenId'] = $custom['account'];
                }
            }
            $data[$k]['actor']['permissions'] = ['sign'];



        }
//        $lovesigning = new Lovesigning();


            //确认签署任务

//            foreach($res['data'] as $k=>$v){
//                $signuser = Db::name('contract_signing')
//                    ->where('contract_id','=',$contract['id'])
//                    ->where('account','=',$v['account'])
//                    ->find();
//                $editdata['signUrl'] = $v['signUrl'];
//                $editdata['account'] = $signuser['account'];
//
//                $this->operatesigning($contract['id'],$editdata,$signuser['id']);
//            }


        return true;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月20月 16:30:28
     * ps:获取合同链接
     */
    public function getapicontracturl($contractId){
        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        $fadada = new Fadada();
        $res = $fadada->getpreviewurl($contract['taskId']);

        return $res;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 10:22:33
     * ps:作废合同
     */
    public function cancelcontract($contractId,$reason){

        $contract = Db::name('contract')->where('id','=',$contractId)->find();

        if($contract['initiateType'] == 'enterprise'){
            $enter = Db::name('enterprise')->where('id','=',$contract['initiate_id'])->find();
            $abolishedInitiator['initiatorId'] = $enter['account'];
        }else{
            $custom = Db::name('custom')->where('id','=',$contract['initiate_id'])->find();
            $abolishedInitiator['initiatorId'] = $custom['account'];
        }
        //$reason = '测试作废';

        $fadada = new Fadada();
        $res = $fadada->abolish($contract['taskId'],$reason,$abolishedInitiator);
        if($res['code'] == 200){
            $edit['state'] = 6;
            $edit['cancelId'] = $res['cancelId'];
            $edit['updatetime'] = time();
            $edit['canceltime'] = time();
            Db::name('contract')->where('id','=',$contractId)->update($edit);

        }
        return $res;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 10:22:33
     * ps:撤销合同
     */
    public function revokecontract($contractId,$msg=''){
        $contract = Db::name('contract')->where('id','=',$contractId)->find();

        $fadada = new Fadada();
        $res = $fadada->cancel($contract['taskId'],$msg);
        if($res['code'] == 200){
            $edit['state'] = 7;
            $edit['updatetime'] = time();
            Db::name('contract')->where('id','=',$contractId)->update($edit);
        }
        return $res;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 17:06:08
     * ps:申请报告
     */
    public function applicationreport($contractId=117){
        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        $fadada = new Fadada();
        $owner = array();
        if($contract['initiateType'] == 'enterprise'){
            $enter = Db::name('enterprise')->where('id','=',$contract['initiate_id'])->find();
            $owner['idType'] = 'corp';
            $owner['openId'] = $enter['account'];

        }else{
            $custom = Db::name('custom')->where('id','=',$contract['initiate_id'])->find();
            $owner['idType'] = 'corp';
            $owner['openId'] = $custom['account'];

        }

        $res = $fadada->signtaskapplyreport($contract['taskId'],$owner,'evidence_report');
        dump($res);exit;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 17:08:49
     * ps:下载印章图片
     */
    public function xiazchuz($imageUrl='https://zip-test-os1.fadada.com/09ac59bfc8ca4f888a8acc68c15c1467/1727164952989133972.zip?sign=q-sign-algorithm%3Dsha1%26q-ak%3DAKIDUmoZ3GI3XkgKhmb9QP9dqlPmshFXXGit%26q-sign-time%3D1729585645%3B1729672045%26q-key-time%3D1729585645%3B1729672045%26q-header-list%3Dhost%26q-url-param-list%3Dresponse-cache-control%3Bresponse-content-disposition%3Bresponse-content-type%3Bresponse-expires%26q-signature%3D3c7dd024c6b882eb8fbc0a0054965a0d4c1ff06c&amp;response-cache-control=no-store%2C%20no-cache%2C%20must-revalidate&amp;response-content-disposition=attachment%3Bfilename%3D%221727164952989133972.zip%22&amp;response-expires=0&amp;response-content-type=application%2Fzip',$sealNo=123){
        // 要保存图片的本地路径和文件名
        $localPath = 'signature/'.$sealNo.'.pdf';
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
     * time:2024年10月24月 16:41:25
     * ps:发起签署
     */
    public function initiatesign($ids){
        $contract = Db::name('contract')->where('id','=',$ids)->find();
        $fadada = new Fadada();
        $fadada->startfill($contract['taskId']);

        $account = Db::name('account')->where('type','=',$contract['initiateType'])->where('type_id','=',$contract['initiate_id'])->find();

        //扣除账户合同份数
        $acedit['contract'] = $account['contract'] -1;
        $acedit['usecontract'] = $account['usecontract'] +1;
        $acedit['updatetime'] = time();
        Db::name('account')->where('type','=',$contract['initiateType'])->where('type_id','=',$contract['initiate_id'])->update($acedit);
        //发送短信验证码
        $sms = new Csms();
        $sms->initiatecontract($ids);
        return true;

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月24月 16:41:25
     * ps:删除合同
     */
    public function delcontract($ids){
        $contract = Db::name('contract')->where('id','=',$ids)->field('taskId')->find();
        Db::name('contract')->where('id','=',$ids)->delete();
        Db::name('contract_annex')->where('contract_id','=',$ids)->delete();
        Db::name('contract_signing')->where('contract_id','=',$ids)->delete();
        Db::name('contract_macf')->where('contract_id','=',$ids)->delete();
        Db::name('contract_template_content')->where('contract_id','=',$ids)->delete();
        //操作法大大文件删除
        if($contract['taskId']){
            $fadada = new Fadada();
            $fadada->delcontract($contract['taskId']);
        }
        return true;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月31月 15:42:19
     * ps:获取合同任务详情
     */
    public function getcontracttask($ids){
        $contract = Db::name('contract')->where('id','=',$ids)->field('taskId')->find();
        $fadada = new Fadada();
        $res = $fadada->gettaskdetaill($contract['taskId']);
        return $res;
    }
}
