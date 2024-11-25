<?php

namespace app\api\controller;


use think\Controller;

/**
 * 爱签接口
 */
class Lovesigning extends Controller
{
    public $host="https://prev.asign.cn/";//请求网关 测试


    public $notifyUrl = "";
    public $appId = '437584575';//应用id测试


    public $apiSecret = "MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCCzCPQgO0SoTFOUkAEThVBI2RSvqE0WOgJLRFTLwa4tQFIuwiorxRp3YSbpIn9OuwJGLgun1Hczz3qrh121wXr4SpIXQRUNbVJeYlulgzFQ5Q/rEAWllIrGKwgZ9/BHxONxFLf2f0YVt0S90R/ETYyaoauZPcYYVij41wfmusNJuXgQFqpO8RL1uRhMTPSyRXzjdl7rm1JJOsGhCQ2OfZ1inoeRUCwPPqp/HhpTYXhOYvNIs/mgKm2o6AZTmZbMEzxEnphyWCQdgSnuHHC3xVfTZr6Mo7JfRSmhGnGC1qmXKBrKbRRLLDGWNqjwImGsiZPDsKvIJEVngc1mQFdqiDHAgMBAAECggEANNWTTgEWQqU8Tn/o/hQwf7x1JPt+ELAtIq/CxNBFLc1n9GIg0ErQuybRDzH6z4DCobYLiEGxBrnsL+UfX8bhzHOK6eow+ncrgL+IZVRVWkW/F61Twgv8qw3vUbPD7bXI50Y7l9LtaqyD5spdL9rbAqiHOODt8zo3XRRVPSsN8aR9+7ALv901k/R6EaVIDC2mHTpfOJw4MzBkie05RcqldJsT/KMU2/gBbh5sVQuxsPcpVc9kL1WigwCnus/NjVGn9FjMLLsf1qbsmki8sFVcjDI21K0c6J+eQsFGxLdbgFfFWjle8mv6//GUMVjv5GpHMe55QX3GjtM+Gvg+6FVWYQKBgQD90PYXreQaWt253FNHG1yRxuRVy1nVZCfpSZjlDzX4nD/B3ipN6mVR7cwhNzS1B0bF5kFxQmEyFD6XvUSZFixVr/QCFXm0Eydn6g4VPnEIzsZ0O3pH4MlHzpk4Wxh20atAtg1HLBonjLbadPAg+zggZrMydZq3CI6KEBpFVNxuVwKBgQCD7Dm9nGGUynnAO3njnMQYxLZQPkt55Lssq7LUIb8AdRL5i+tRUXPtGjk7nJXTT5aOCYgLuzArVvmUY72FALZv3IgctMuQMlEV28Z5F5dIujMCqps2WLAsybBJXvnPzoLOEufAPjwsBjD2za9fX8GCWhA9ehS9FTblTW4BljV7EQKBgEbgnGgeYg1OBI7LTOIVbPM0ZDzlDU/+qPqHV8/XQI4NK+y6WnvpkaOgURmRbgGDZ6sJ0oqLK9MtPhFnhAlv3K+M9AnE73huxNlKzeX2yt/XxildFpeN2QdZVQYcwickA7uNWwXd9evHaqR0dT3wiUrbAv17Q9oK5Kr/NibYPLn/AoGADnFjVO31BPwx3ijkzFWSZn/K0fgv/TVchKR7nJvhNGSc4jM+XRXE0lWHpI4dHRhejEhg25/vwx7vjh5pVlFgp9iGElZ83tmTZQg9r240wuKXyRfyjD2jdBPUuAOs5+JdEcCiHLrzjYJUBAE6zP9HyUSg+IoQES9sZihW/dd7HXECgYEAxaPWP/YVR1YQHjY9IZnubJpUyIwGat/99N4W44a4/DLdpAfF81U5wkIrCVDXUueEolPb+wSypA91QULbORImWmHxjIXbuQtccRNUvNASTeICb2kGzBfZSgxfYFcYoCX2eZsHkaoosTmNVoxSrrwDORjERml5HnsYRGeTG/6fhok=";
    public function __construct()
    {
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 13:44:46
     * ps:个人实名认证网页版
     * url:{{URL}}/index.php/api/lovesigning/userattestationurl
     */
    public function userattestationurl($name='',$identityNo='',$phone='',$customId){
        $data = array();

        $url = '/auth/person/identifyUrl';

        $data['realName'] = $name;
        $data['idCardNo'] = $identityNo;
        $data['mobile'] =$phone;
        $data['redirectUrl'] ='http://sign.xtwlhy.com';
        $data['bizId'] =$customId.'-'.rand(0,1000);
        $data['notifyUrl'] ='http://sign.xtwlhy.com//index.php/index/signnotifyls';
        $res  =  $this->request($data,$url);

        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['serialNo']=$res['data']['serialNo'];
                $rest['identifyUrl']=$res['data']['identifyUrl'];
                $rest['code'] = 200;
            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = '网络错误请稍后重试。';
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 13:43:49
     * ps:个人要素认证(运营商三要素、银行卡四要素)
     * url:{{URL}}/index.php/api/lovesigning/userfactorattestation
     *$name:姓名；$identityNo:身份证号；$phone:手机号；$bankNo:银行卡号；$type:类型（1：运营商三要素认证；2：银行卡四要素认证）
     */
    public function userfactorattestation($name='',$identityNo='',$phone='',$bankNo='',$type=1){
        $data = array();
        if($type == 1){
            $url = '/auth/person/mobile3';
        }else{
            $url = '/auth/person/bankCard4';
            $data['bankCard'] = $bankNo;
        }
        $data['realName'] = $name;
        $data['idCardNo'] = $identityNo;
        $data['mobile'] =$phone;
        $res  =  $this->request($data,$url);
        if(isset($res['data']['code'])){
            $rest['msg'] = $res['data']['msg'];
            $rest['code'] = 300;
            if($res['data']['code'] == '100000'){
                $rest['serialNo']=$res['data']['data']['serialNo'];
                $rest['code'] = 200;
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 14:42:15
     * ps:个人人脸活体认证（可以做回调接口）
     * url:{{URL}}/index.php/api/lovesigning/userfaceattestation
     * $name:真实姓名；$identityNo:身份证号
     * $cardtype:证件类型（1：居民身份证；2：台湾居民来往大陆通行证；3:港澳居民来往内地通行证)；
     * $redirecturl：重定向地址；$faceAuthMode:认证渠道（1:支付宝；2：h5;4:微信小程序；5：支付宝小程序；
     * $showResult:是否展示人脸认证结果（1：展示；0：不展示（默认）
     * $userId:用户ID；$isIframe:iframe判定（1：是；0：否（默认））；
     */
    public function userfaceattestation($name,$identityNo,$cardtype,$redirecturl,$faceAuthMode='',$showResult=2,$userId='',$isIframe=1){
        $data = array();
        $url = '/person/person/face';

        $data['realName'] = $name;
        $data['idCardNo'] = $identityNo;
        $data['idCardType'] =$cardtype;
        $data['redirectUrl'] =$redirecturl;
        $data['faceAuthMode'] =$faceAuthMode;
        $data['showResult'] =$showResult;
        $data['bizId'] =$userId;
        $data['isIframe'] =$isIframe;
        $res  =  $this->request($data,$url);
        if(isset($res['data']['code'])){
            $rest['msg'] = $res['data']['msg'];
            $rest['code'] = 300;
            if($res['data']['code'] == '100000'){
                $rest['serialNo']=$res['data']['data']['serialNo'];//认证流水号
                $rest['faceUrl']=$res['data']['data']['faceUrl'];//人脸识别链接

                $rest['code'] = 200;
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 15:11:17
     * ps:个人意愿核身认证（可以做回调接口）
     * url:{{URL}}/index.php/api/lovesigning/usernuclearbodyattestation
     * $name:真实姓名；
     * $identityNo:身份证号
     * $question:意愿核身过程中的播报文本，长度120以内
     * $answer：用户回答文本，单个回答长度10以内，支持多个文本作为识别内容，文本之间用“|”分割，总长度32以内默认为：“我确认|是的”。
     * $redirectUrl:人脸认证结果回调通知URL，可拼接参数
     */
    public function usernuclearbodyattestation($name='',$identityNo='',$question='',$answer='',$redirecturl=''){
        $data = array();
        $url = '/person/person/willFace';

        $data['realName'] = $name;
        $data['idCardNo'] = $identityNo;
        $data['question'] =$question;
        $data['answer'] =$answer;
        $data['redirectUrl'] =$redirecturl;

        $res  =  $this->request($data,$url);
        if(isset($res['data']['code'])){
            $rest['msg'] = $res['data']['msg'];
            $rest['code'] = 300;
            if($res['data']['code'] == '100000'){
                $rest['serialNo']=$res['data']['data']['serialNo'];//认证流水号
                $rest['faceUrl']=$res['data']['data']['faceUrl'];//意愿核身链接

                $rest['code'] = 200;
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 15:31:43
     * ps:企业认证网页版
     * url:{{URL}}/index.php/api/lovesigning/enterattestation
     */
    public function enterattestationurl($entername='滁州泓通物联科技有限公司',$enterCode='91341102MADUP1GT6H',$name='李淑娟',$identityNo='220519197210013361',$enterId=1){
        $data = array();
        $url = '/auth/company/identifyUrl';
        $data['companyName'] = $entername;
        $data['creditCode'] = $enterCode;
        $data['legalPersonName'] =$name;
        $data['legalPersonIdCardNo'] =$identityNo;



        //$data['redirectUrl'] ='http://sign.xtwlhy.com';
        //$data['bizId'] =$enterId.'-'.rand(0,1000);
        //$data['notifyUrl'] ='http://sign.xtwlhy.com//index.php/index/signnotifyls/enterprise';
        $res  =  $this->request($data,$url);
        dump($res);exit;
        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['serialNo']=$res['data']['serialNo'];
                $rest['identifyUrl']=$res['data']['identifyUrl'];
                $rest['code'] = 200;
            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = '网络错误请稍后重试。';
        }
        return $rest;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 15:31:43
     * ps:企业法人认证（运营商三要素、银行卡四要素）
     * url:{{URL}}/index.php/api/lovesigning/enterattestation
     */
    public function enterattestation($entername='',$enterCode='',$name='',$identityNo='',$phone='',$bankNo='',$type=1){
        $data = array();
        if($type == 1){
            $url = '/person/company/mobile3';
        }else{
            $url = '/person/company/bankCard4';
            $data['bankCard'] = $bankNo;
        }
        $data['companyName'] = $entername;
        $data['creditCode'] = $enterCode;
        $data['realName'] = $name;
        $data['idCardNo'] = $identityNo;
        $data['mobile'] =$phone;
        $res  =  $this->request($data,$url);
        if(isset($res['data']['code'])){
            $rest['msg'] = $res['data']['msg'];
            $rest['code'] = 300;
            if($res['data']['code'] == '100000'){
                $rest['serialNo']=$res['data']['data']['serialNo'];
                $rest['code'] = 200;
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 14:42:15
     * ps:企业法人人脸活体认证（可以做回调接口）
     * url:{{URL}}/index.php/api/lovesigning/userfaceattestation
     * $name:真实姓名；$identityNo:身份证号
     * $cardtype:证件类型（1：居民身份证；2：台湾居民来往大陆通行证；3:港澳居民来往内地通行证)；
     * $redirecturl：重定向地址；$faceAuthMode:认证渠道（1:支付宝；2：h5;4:微信小程序；5：支付宝小程序；
     * $showResult:是否展示人脸认证结果（1：展示；0：不展示（默认）
     * $userId:用户ID；$isIframe:iframe判定（1：是；0：否（默认））；
     */
    public function enterfaceattestation($entername='',$enterCode='',$name='',$identityNo='',$cardtype='',$redirecturl='',$faceAuthMode='',$showResult=2,$userId='',$isIframe=1){
        $data = array();
        $url = '/person/company/face';
        $data['companyName'] = $entername;
        $data['creditCode'] = $enterCode;
        $data['realName'] = $name;
        $data['idCardNo'] = $identityNo;
        $data['idCardType'] =$cardtype;
        $data['redirectUrl'] =$redirecturl;
        $data['faceAuthMode'] =$faceAuthMode;
        $data['showResult'] =$showResult;
        $data['bizId'] =$userId;
        $data['isIframe'] =$isIframe;
        $res  =  $this->request($data,$url);
        if(isset($res['data']['code'])){
            $rest['msg'] = $res['data']['msg'];
            $rest['code'] = 300;
            if($res['data']['code'] == '100000'){
                $rest['serialNo']=$res['data']['data']['serialNo'];//认证流水号
                $rest['faceUrl']=$res['data']['data']['faceUrl'];//人脸识别链接

                $rest['code'] = 200;
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 15:37:17
     * ps:验证认证验证码
     * url:{{URL}}/index.php/api/lovesigning/validateattestation
     * $serialNo：认证流水号
     * $captcha：认证验证码
     */
    public function validateattestation($serialNo='',$captcha=''){
        $data = array();
        $url = '/auth/captcha/verify';
        $data['serialNo'] = $serialNo;
        $data['captcha'] = $captcha;
        $res  =  $this->request($data,$url);
        if(isset($res['data']['code'])){
            $rest['msg'] = $res['data']['msg'];
            $rest['code'] = 300;
            if($res['data']['code'] == '100000'){
                $rest['serialNo']=$res['data']['data']['serialNo'];//认证流水号
                $rest['result']=$res['data']['data']['result'];//认证结果
                $rest['code'] = 200;
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 15:40:08
     * ps:重新发送认证验证码
     * url:{{URL}}/index.php/api/lovesigning/resend
     * $serialNo：认证流水号
     */
    public function resend($serialNo){
        $data = array();
        $url = '/auth/captcha/resend';
        $data['serialNo'] = $serialNo;
        $res  =  $this->request($data,$url);
        if(isset($res['data']['code'])){
            $rest['msg'] = $res['data']['msg'];
            $rest['code'] = 300;
            if($res['data']['code'] == '100000'){
                $rest['code'] = 200;
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 16:09:07
     * ps:获取实名认证信息
     * url:{{URL}}/index.php/api/lovesigning
     */
    public function getattestation($serialNo){
        $data = array();
        $url = '/auth/getAuthRecordInfo';
        $data['serialNo'] = $serialNo;
        $res  =  $this->request($data,$url);
        if(isset($res['data']['code'])){
            $rest['msg'] = $res['data']['msg'];
            $rest['code'] = 300;
            if($res['data']['code'] == '100000'){
                $rest['code'] = 200;
                $rest['result'] = $res['data']['result'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 16:09:07
     * ps:2024年8月29月 16:18:28
     * url:{{URL}}/index.php/api/lovesigning
     */
    public function getfaceresult($serialNo){
        $data = array();
        $url = '/user/faceResult';
        $data['serialNo'] = $serialNo;
        $res  =  $this->request($data,$url);
        if(isset($res['data']['code'])){
            $rest['msg'] = $res['data']['msg'];
            $rest['code'] = 300;
            if($res['data']['code'] == '100000'){
                $rest['code'] = 200;
                $rest['status'] = $res['data']['status'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 13:39:25
     * ps:添加个人用户
     * url:{{URL}}/index.php/api/lovesigning/adduser
     */
    public function adduser($account='',$serialNo=''){
        $data = array();
        $url = '/v2/user/addPersonalUser';
        $data['account'] = $account;
        $data['serialNo'] = $serialNo;
        $res  =  $this->request($data,$url);
        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['code'] = 200;
                $rest['sealNo'] = $res['data']['sealNo'];//默认印章编号
            }else{
                if($res['data']['code'] == '100021'){
                    //用户已存在
                    $rest['code'] = 201;
                    $rest['msg'] = '用户已存在';

                }else{
                    $rest['msg'] = '添加用户失败';

                }
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = '网络错误请稍后重试。';
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 17:57:48
     * ps:获取用户信息
     * url:{{URL}}/index.php/api/lovesigning/getuser
     * $account:用户唯一识别号
     * $creditCode:社会统一信用代码
     * $idCard：证件号
     * $type:查询类型（1：用户统一识别号；2：社会统一信用代码；3：证件号
     * $usertype:用户类型（1：企业；2：个人）
     */
    public function getuser($biaos,$type=1,$usertype){
        $data = array();
        $url = '/user/getUser';
        if($type == 1){
            $data['account'] = $biaos;
        }else if($type == 2){
            $data['creditCode'] = $biaos;
        }else if($type == 3){
            $data['idCard'] = $biaos;
        }

        $res = $this->request($data,$url);

        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == 'true'){
                $rest['code'] = 200;
                if($usertype == 1){
                    $rest['account'] = $res['data']['account'];
                    $rest['name'] = $res['data']['name'];
                    $rest['identityNo'] = $res['data']['idCard'];
                    $rest['phone'] = $res['data']['mobile'];
                    $rest['attestation'] = $res['data']['status'];//认证状态（0：未认证；1：已认证)
                    $rest['attestationType'] = $res['data']['identifyType'];
                    $rest['finishedTime'] = strtotime($res['data']['identifyTime']);
                    $rest['serialNo'] = $res['data']['serialNo'];
                    $rest['companyName'] = $res['data']['companyName'];
                    $rest['creditCode'] = $res['data']['creditCode'];


                }else{
                        if($res['data']['userType']==$usertype){
                            $rest['account'] = $res['data']['account'];
                            $rest['name'] = $res['data']['name'];
                            $rest['identityNo'] = $res['data']['idCard'];
                            $rest['phone'] = $res['data']['mobile'];
                            $rest['attestation'] = $res['data']['status'];//认证状态（0：未认证；1：已认证)
                            $rest['attestationType'] = $res['data']['identifyType'];
                            $rest['finishedTime'] = strtotime($res['data']['identifyTime']);
                            $rest['serialNo'] = $res['data']['serialNo'];
                        }
                }
            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = '网络错误请稍后重试';
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年8月29月 13:39:25
     * ps:添加企业用户
     * url:{{URL}}/index.php/api/lovesigning/addenter
     */
    public function addenter($account='91341102MADUP1GT6H',$serialNo='CA35020241115095019052493',$name='李淑娟',$idCard='220519197210013361',$mobile='18904383601'){
        $data = array();
        $url = '/v2/user/addEnterpriseUser';
        $data['account'] = $account;
        $data['serialNo'] = $serialNo;

        $data['name'] = $name;
        $data['idCard'] = $idCard;
        $data['mobile'] = $mobile;


        $res  =  $this->request($data,$url);
        dump($res);exit;
        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['code'] = 200;
                $rest['sealNo'] = $res['data']['sealNo'];//默认印章编号
            }else{
                if($res['data']['code'] == '100021'){
                    //用户已存在
                    $rest['code'] = 201;
                    $rest['msg'] = '用户已存在';

                }else{
                    $rest['msg'] = '添加用户失败';

                }

            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] = '网络错误请稍后重试。';
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 16:09:14
     * ps:查询印章
     * url:{{URL}}/index.php/api/lovesigning/getseals
     * $account:用户唯一标识
     * $sealNo:印章编号
     */
    public function getseals($account,$sealNo){
        $data = array();
        $url = '/user/getUserSeals';
        $data['account'] = $account;
        $data['sealNo'] = $sealNo;
        $res  =  $this->request($data,$url);

        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['code'] = 200;
                if($sealNo){
                    if($res['data']['list']){
                        $rest['sealNo'] = $res['data']['list'][0]['sealNo'];//印章编号
                        $rest['sealUrl'] = $res['data']['list'][0]['sealUrl'];//印章图片地址
                        $rest['isDefault'] = $res['data']['list'][0]['isDefault'];//是否默认
                        $rest['sealName'] = $res['data']['list'][0]['sealName'];//印章名称
                    }

                }else{
                    foreach($res['data']['list'] as $k=>$v){
                        $rest['list'][$k]['sealNo'] = $v['sealNo'];//印章编号
                        $rest['list'][$k]['sealUrl'] = $v['sealUrl'];//印章图片地址
                        $rest['list'][$k]['isDefault'] = $v['isDefault'];//是否默认
                        $rest['list'][$k]['sealName'] = $v['sealName'];//印章名称
                    }
                }
            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] ='网络错误请稍后重试';
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 9:29:43
     * url:{{URL}}/index.php/api/lovesigning/addseals
     * ps:添加签章
     * $account:用户唯一标识
     * $type:类型（custom：个人，enterprise：企业)
     */
    public function addseals($account,$type){
        $data = array();
        if($type == 'custom'){
            $url = '/seal/makePersonSealOnline';
            $data['hwBoardType'] = 1;
        }else{
            $url = '/seal/makeOnline';

        }
        $data['account'] = $account;
        $data['redirectUrl'] = 'http://sign.xtwlhy.com';//前台返回地址
        $data['notifyUrl'] = 'http://sign.xtwlhy.com/index.php/index/signnotifyls/signature';//后台异步通知地址

        $res  =  $this->request($data,$url);

        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['code'] = 200;
                if($type == 'custom'){
                    $rest['hwBoardUrl'] = $res['data']['hwBoardUrl'];//在线制作印章链接（有效期3小时）
                }else{
                    $rest['hwBoardUrl'] = $res['data'];//在线制作印章链接（有效期3小时）
                }
                //$rest['bizImgNo'] = $res['data']['bizImgNo'];//制作印章返回信息
            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] ='网络错误请稍后重试';
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 9:10:34
     * ps:设置默认章
     */
    public function setDefaultSeal($account,$sealNo){
        $data = array();
        $url = '/user/setDefaultSeal';
        $data['account'] = $account;
        $data['sealNo'] = $sealNo;
        $res  =  $this->request($data,$url);


        $rest['code'] = 300;
        if($res['rt'] == true){
            $rest['code'] = 200;
        }else{
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月10月 16:20:15
     * ps:删除印章
     */
    public function removeSeal($account,$sealNo){
        $data = array();
        $url = '/user/removeSeal';
        $data['account'] = $account;
        $data['sealNo'] = $sealNo;
        $res  =  $this->request($data,$url);


        $rest['code'] = 300;
        if($res['rt'] == true){
            $rest['code'] = 200;
        }else{
            $rest['msg'] = $res['data']['msg'];
        }
        return $rest;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 16:09:14
     * ps:发起合同（页面版)
     * url:{{URL}}/index.php/api/lovesigning/initiateUrl
     */
    public function initiateUrl($contractNo,$account){
        $data = array();
        $url = '/v2/contract/initiateUrl';
        $data['contractConfig']['contractNo'] = $contractNo;
        //签署配置
        $data['signConfig']['initializedNotice'] = 1;//合同签署链接短信通知（0：否；1：是）
        $data['signConfig']['completedNotice'] = 1;//签署完成是否通知用户（0：否；1：是)

        //后台回调配置
        $data['notifyConfig']['completedUrl'] = 'http://sign.xtwlhy.com/index.php/index/signnotifyls/signoff';
        $data['notifyConfig']['failedUrl'] = 'http://sign.xtwlhy.com/index.php/index/signnotifyls/signingfailed';
        $data['notifyConfig']['userSignedUrl'] = 'http://sign.xtwlhy.com/index.php/index/signnotifyls/singlesignoff';

        //前台跳转
        $data['redirectConfig']['signedUrl'] = 'http://sign.xtwlhy.com';
        $data['redirectConfig']['initializedUrl'] = 'http://sign.xtwlhy.com';

        //添加合同签署方
        $data['signers'][0]['account'] = $account;
        $res  =  $this->request($data,$url);


        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['code'] = 200;
                $rest['initiateUrl'] = $res['data']['initiateUrl'];//发起签约地址
                $rest['contractNo'] = $res['data']['contractNo'];//合同编号

            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] ='网络错误请稍后重试';
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月05月 13:01:51
     * ps:获取合同信息
     */
    public function getcontract($contractNo){
        $data = array();
        $url = '/contract/getContract';
        $data['contractNo'] = $contractNo;
        $res  =  $this->request($data,$url);

        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['code'] = 200;
                $rest['contractNo'] = $res['data']['contractNo'];
                $rest['status'] = $res['data']['status'];
                $rest['contractName'] = $res['data']['contractName'];
                $rest['expireTime'] = strtotime($res['data']['validityTime']);
                $rest['shortUrl'] = $res['data']['previewUrl'];
                foreach($res['data']['signUser'] as $k=>$v){

                    $rest['signing'][$k]['account'] = $v['account'];
                    $rest['signing'][$k]['signUrl'] = $v['signUrl'];
                    $rest['signing'][$k]['signOrder'] = $v['signOrder'];
                    $rest['signing'][$k]['name'] = $v['name'];
                    if($v['userType'] == 1){
                        $rest['signing'][$k]['enterName'] = $v['companyName'];

                    }else{
                        $rest['signing'][$k]['enterName'] = '';

                    }
                    if(isset($v['signFinishedTime'])){
                        $rest['signing'][$k]['identityNo'] = $v['idCard'];
                    }else{
                        $rest['signing'][$k]['identityNo'] ='';
                    }

                    $rest['signing'][$k]['phone'] = $v['mobile'];
                    $rest['signing'][$k]['userType'] = $v['userType'];
                    $rest['signing'][$k]['sealNo'] = $v['sealNo'];
                    $rest['signing'][$k]['signType'] = $v['signType'];
                    $rest['signing'][$k]['validateType'] = $v['validateType'];
                    $rest['signing'][$k]['state'] = $v['signStatus'];
                    if(isset($v['signFinishedTime'])){
                        $rest['signing'][$k]['signTime'] = strtotime($v['signFinishedTime']);

                    }else{
                        $rest['signing'][$k]['signTime'] ='';

                    }

                }


            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] ='网络错误请稍后重试';
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 10:13:14
     * ps:下载合同
     */
    public function downloadContract($contractNo){
        $data = array();
        $url = '/contract/downloadContract';
        $data['contractNo'] = $contractNo;
        $data['downloadFileType'] = 1;
        $res  =  $this->request($data,$url);

        $rest['code'] = 300;
        if($res['rt'] == true){
            $rest['code'] = 200;
            $rest['fileName'] = $res['data']['fileName'];
            $rest['md5'] = $res['data']['md5'];
            $rest['fileType'] = $res['data']['fileType'];
            $rest['size'] = $res['data']['size'];
            $rest['data'] = $res['data']['data'];

        }else{
            $rest['msg'] = $res['data']['msg'];
        }

        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 15:23:56
     * ps:获取模板填充信息
     * url:{{URL}}/index.php/lovesigning/gettemplateinfo
     */
    public function gettemplateinfo($templateldent){
        $data = array();
        $url = '/template/data';
        $data['templateIdent'] = $templateldent;
        $res  =  $this->request($data,$url);

        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['code'] = 200;


            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] ='网络错误请稍后重试';
        }
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月07月 15:38:24
     * ps:创建待签署文件（模版）
     */
    public function createContract($contractNo,$contractName,$validityDate,$templateNo,$fillData){
        $data = array();
        $url = '/contract/createContract';
        $data['contractNo'] = $contractNo;
        $data['contractName'] = $contractName;
        $data['validityDate'] = $validityDate;
        $data["templates"]['templateNo'] = $templateNo;
        $data["templates"]["fillData"] = $fillData;
        $data['signOrder'] = 1;

        $res  =  $this->request($data,$url);

        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['code'] = 200;
                $rest['contractFile'] = $res['data']['previewUrl'];

            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] ='网络错误请稍后重试';
        }
        return $rest;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月07月 22:48:26
     * ps:获取模板填充信息
     * url:{{URL}}/index.php/lovesigning/addSigner
     */
    public function addSigner($data){

        $url = '/contract/addSigner';

        $res  =  $this->request($data,$url);

        if(isset($res['data'])){
            $rest['code'] = 300;
            if($res['rt'] == true){
                $rest['code'] = 200;
                foreach($res['data']['signUser'] as $k=>$v){
                    $rest['data'][$k]['account'] = $v['account'];
                    $rest['data'][$k]['signUrl'] = $v['signUrl'];
                }

            }else{
                $rest['msg'] = $res['data']['msg'];
            }
        }else{
            $rest['code'] = 300;
            $rest['msg'] ='网络错误请稍后重试';
        }
        return $rest;
    }
    /**
     * @author: Neupres·lin
     * @name: request
     * @description: api请求封装 函数步骤 对参数排序->生成签名->使用phpcurl发送带签名的请求->处理返回
     * @param {*}
     * @return {*}
     */
    protected function request($data,$url) {
        // 去掉空值key
        ksort($data);
        $data = $this->filterEmpty($data);
        //对参数进行排序
        //$this->sortField();
        $data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
//        echo PHP_EOL;
//        var_dump($data);
        $time    = $this->msectime() + 600 * 1000;
        $signStr =$data . md5($data) . $this->appId . $time;
        $sign    = $this->sign($signStr, $this->apiSecret);

        $headers    = [
            "sign:" . $sign,
            // "Content-Type:application/form-data;",
        ];

        $postFields = ['bizData' =>$data, 'appId' => $this->appId, 'timestamp' => $time];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->host.$url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resData = curl_exec($curl);
//        echo PHP_EOL .$resData;
        $resData = json_decode($resData, true);
        if (empty($resData) || $resData['code'] != '100000') {
            $res = [
                'rt'   => false,
                'data' => $resData,
            ];
        } else {
            $res = [
                'rt'   => true,
                'data' => $resData['data'],
            ];
        }
        return $res;
    }
    /**
     * @author: Neupres·lin
     * @name: 工具箱
     * @description: 包含接口常用工具
     * @param {*}
     * @return {*}
     */
    //时间戳protected
    public function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
    //ras非对称加密
    protected function sign($signString,$priKey) {
        $priKey    = "-----BEGIN PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END PRIVATE KEY-----";
        //var_dump(extension_loaded('openssl'));
        $privKeyId = openssl_pkey_get_private($priKey);
        $signature = '';
        openssl_sign($signString, $signature, $privKeyId);
        //openssl_free_key($privKeyId);
        //var_dump("------------".$privKeyId);
        return base64_encode($signature);
    }
    //ras验证
    protected function verify($data, $signString, $pubKey) {
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        $pubKey = openssl_pkey_get_public($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($signString), $pubKey);
        //openssl_free_key($pubKey);
        return $result;
    }
    /**
     * 去掉空值的key。
     *
     * @param $obj
     * @return array
     */
    public function filterEmpty($data) {
        $obj = $data;
        // 是数组
        if(is_array($obj)) {
            ksort($obj);
            foreach ($obj as $k => $v) {
                if(is_object($obj[$k]) || is_array($obj[$k])){
                    $obj[$k]= $this->filterEmpty($obj[$k]);
                }else {
                    if($v === null) {
                        unset($obj[$k]);
                    }
                }
            }
        }else {
            foreach ($obj as $k => $v) {
                if(is_object($obj->$k) || is_array($obj->$k)){
                    $obj->$k = $this->filterEmpty($obj->$k);
                }else if(!$v) {
                    unset($obj->$k);
                }
            }
        }

        return $obj;
    }

}
