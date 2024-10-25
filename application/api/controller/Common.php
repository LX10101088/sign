<?php

namespace app\api\controller;


use app\common\controller\Commonattestation;
use app\common\controller\Commoncontract;
use app\common\controller\Commonenter;
use app\common\controller\Commonsignature;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use think\Controller;
use think\Db;
use PhpOffice\PhpWord\Shared\ZipArchive;


/**
 * 公共接口
 */
class Common extends Gathercontroller
{


    public function _initialize()
    {


        parent::_initialize();
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST, GET, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers:*');
        header('Access-Control-Expose-Headers: *');
    }

    /**
     * Created by PhpStorm.
     * User: lang
     * time:2021年4月27日 14:19:29
     * ps:图片上传
     * url：{{URL}}/api/Common/upload_one
     */
    public function upload_one()
    {
        $file = request()->file('file');

        $path = "/upload/image/";
        if ($file === null) {
            ajaxReturn(['code' => 300, 'msg' => '图片不能为空']);
        }
        $info = $file->validate(['seize' => 2097152, 'ext' => 'jpg,png,gif,jpeg,pdf,doc,docx'])->move('./' . $path);
        if ($info) {
            $file_name = request()->domain()  . $path . $info->getSaveName();

            $res['code'] = 200;
            $res['msg'] = "请求成功";
            $res['data']['filename'] = $file_name;
            echo json_encode($res);
            exit;
            ajaxReturn(['code' => 200, 'msg' => '请求成功', 'data' => ['filename' => $image]]);
        } else {
            $res['code'] = 300;
            $res['msg'] = $file->getError();
            echo json_encode($res);
            exit;
            ajaxReturn(['code' => 300, 'msg' => $file->getError()]);
        }

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月11月 15:56:57
     * ps:获取客服信息
     * url:{{URL}}/index.php/api/common/getkf
     */
    public function getkf(){
        $setup = Db::name('platform_setup')->where('enterprise_id','=',$this->platformId)->find();
        //$url = 'https://sign.obsend.com/upload/image/20240919/70eb6c0ddebc16a32c0eb72f381afb10.jpg';
        $url = $setup['kefu'];
        ajaxReturn(['code'=>200,'msg'=>'获取成功','url'=>$url]);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月17月 9:40:02
     * ps:获取平台设置内容
     * url:{{URL}}/index.php/api/common/getsetup
     */
    public function getsetup(){
        $setup = Db::name('platform_setup')->where('enterprise_id','=',$this->platformId)->find();
        $data['platformName'] = $setup['platformName'];
        $data['logo'] = $setup['logo'];
        $data['kefu'] = $setup['kefu'];
        ajaxReturn(['code'=>200,'msg'=>'获取成功','data'=>$data]);

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月20月 10:37:09
     * ps:获取轮播图列表
     * url:{{URL}}/index.php/api/common/getbanner
     */
    public function getbanner(){

        $banner = Db::name('banner')->where('status','=','normal')->where('enterprise_id','=',$this->platformId)->order('weigh desc')->select();
        $data = array();
        foreach($banner as $k=>$v){
            $data[$k]['name'] = $v['name'];
            $data[$k]['image'] = $v['image'];
            $data[$k]['link'] = $v['link'];
            $data[$k]['describe'] = $v['describe'];
        }
        ajaxReturn(['code'=>200,'msg'=>'获取成功','data'=>$data]);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月26月 16:27:05
     * ps:获取平台协议
     * url:{{URL}}/index.php/api/common/getprotocol
     */
    public function getprotocol(){
        $type = input('param.type');
        $info = Db::name('information')->where('type','=',$type)->where('enterprise_id','=',$this->platformId)->order('id desc')->find();
        $data = array();
        if($info){
            $data['name'] = $info['name'];
            $data['content'] = $info['content'];
            $data['createtime'] = date('Y-m-d H:i:s',$info['createtime']);

        }
        ajaxReturn(['code'=>200,'msg'=>'获取成功','data'=>$data]);

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年1月15月 10:59:21
     * ps:生成二维码
     * url:{{URL}}/index.php/api/common/addqrcode
     */
    public function addqrcode(){
        $url = input('param.url');
        if(!$url){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $res = $this->qrcode($url);
        $name = date('YmdHis',time()).$this->generateRandomString().'.png';

        // 将二维码字符串保存为图片
        $image = imagecreatefromstring($res);
        $filename = 'qrcode/'.$name; // 保存的文件名
        imagepng($image, $filename);
        // 释放图像资源
        imagedestroy($image);
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $http = 'https://';
        } else {
            $http = 'http://';
        }
        ajaxReturn(['code'=>200,'msg'=>'获取成功','url'=>$http.$_SERVER['HTTP_HOST'].'/'.$filename]);

    }
    function generateRandomString($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function qrcode($url){
        $qrCode = new QrCode($url);
        // 设置二维码参数
        $qrCode->setSize(300); // 设置二维码的大小（像素）
        $qrCode->setMargin(10); // 设置二维码的边距（像素）

        // 获取二维码图片二进制数据
        $qrCodeData = $qrCode->writeString();
        // 将二进制数据输出到浏览器或保存到文件
        //header('Content-Type: '.$qrCode->getContentType());
        return $qrCodeData;
    }
        public function test(){
            //创建印章
            $data['sealNo'] = 'a97e824378fa4360aebbb91da21d43d9';
            $data['createtime'] = time();
            $data['default'] = 1;
            $data['state'] = 1;
            $data['name'] = '默认印章';
            $commonsignature = new Commonsignature();
            $commonsignature->addsignature($data,'custom',1);
        }
    public function test2(){
        //创建印章
        $commonsignature = new Commonsignature();
        $commonsignature->getplatformsignature(6);
    }
    public function test3(){
        //查询用户信息
        $lovesigning = new Lovesigning();
        $res = $lovesigning->getuser('TC256681MABTHN5D72',1,1);
        dump($res);exit;
    }

    public function test4(){
        //查询用户信息
        $lovesigning = new Lovesigning();
        $res = $lovesigning->initiateUrl('HT202409059481111311','TC256681MABTHN5D72');
        dump($res);exit;
    }

    public function test5(){
        //查询用户信息
        $commoncontract = new Commoncontract();
        $res = $commoncontract->getapicontract('HT202409059481113',2,'enterprise',1);
        dump($res);exit;
    }


    public function test6(){
        //查询用户信息
        $lovesigning = new Lovesigning();
        $res = $lovesigning->gettemplateinfo('TN596CDA84E17D4932BB90FAF7B680C24D');
        dump($res);exit;
    }


    public function test7(){
        $fadada = new Fadada();
        $fadada->userattestationurl('郎骁','211282200001143815','13841046298',1);
    }

    public function test8(){
        $fadada = new Fadada();
        $res = $fadada->getuserstate('211282200001143815',2);
        if($res['code'] == 200){
            $data['account'] = $res['account'];
            $data['attestation'] = $res['attestation'];
            $data['updatetime'] = time();
            Db::name('custom')->where('id','=',1)->update($data);
        }
    }

    public function test9(){
        $custom = Db::name('custom')->where('id','=',1)->find();
        $fadada = new Fadada();
        $res = $fadada->getuser($custom['account']);
        dump($res);exit;
        if($res['code'] == 200){
            $data['account'] = $res['account'];
            $data['attestation'] = $res['attestation'];
            $data['updatetime'] = time();
            Db::name('custom')->where('id','=',1)->update($data);
        }
    }

    public function test10(){
        $enter = Db::name('enterprise')->where('id','=',1)->find();
        $commonattestation = new Commonattestation();
        $res= $commonattestation->enterprise(1);
        dump($res);exit;
    }

    public function test11(){
        $enter = Db::name('enterprise')->where('id','=',1)->find();
        $fadada = new Fadada();
        $res = $fadada->getuserstate('91210100MA0U10U784',1);
        dump($res);exit;
    }
    public function test12(){
//        $enter = Db::name('enterprise')->where('id','=',1)->find();
        $fadada = new Fadada();
        $res = $fadada->getuser('91210100MA0U10U784',3,1);
        dump($res);exit;
    }
    public function test121(){
        $enter = Db::name('enterprise')->where('id','=',1)->find();
        $fadada = new Fadada();
        $res = $fadada->getuser($enter['proveNo'],3,1);
        if($res['code'] == 200){
            $data['account'] = $res['account'];
            $data['attestation'] = $res['attestation'];
            $data['updatetime'] = time();
            Db::name('enterprise')->where('id','=',$enter['id'])->update($data);
        }
        dump($res);exit;
    }
    public function test13(){
        $fadada = new Fadada();
        $res = $fadada->addseals('211282200001143815');
        dump($res);exit;
    }

    public function test14(){
        $fadada = new Fadada();
        $res = $fadada->addseals('8e7df3e65607408aad3350c22cb1c128','enterprise','211282200001143815');
        dump($res);exit;
    }

    public function test15(){
        $fadada = new Fadada();
        $res = $fadada->getseals('8e7df3e65607408aad3350c22cb1c128','',1);
        dump($res);exit;
    }

    public function test16(){
        $common = new Commonenter();
        $common->getapienter(1);

    }

    public function test17(){
        $common = new Commonsignature();
        $common->getcertinfo('enterprise',1);

    }

    public function test18(){
        $contractId = 194;
        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        $template = Db::name('template')->where('id','=',$contract['template_id'])->find();

        $fadada = new Fadada();
        $cotecontent = Db::name('contract_template_content')->where('contract_id','=',$contract['id'])->select();
        $taskdetail = $fadada->getcontenttaskdetail($contract['fileId']);
        if($taskdetail['code']==200){
            $docId = $taskdetail['docId'];
        }
        $fillData = array();
        foreach($cotecontent as $k=>$v){
            $fillData[$k]['docId'] =$docId;
            $fillData[$k]['fieldName'] = $v['name'];
            $fillData[$k]['fieldValue'] = $v['content'];
        }
        $rest = $fadada->fillvalues($contract['fileId'],$fillData);
        dump($rest);exit;

    }

    public function test19(){
        $common = new Commoncontract();
        $common->addSigner(71);

    }


    public function test20(){
        $common = new Commoncontract();
        $common->initiatecontractfile(72);

    }

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



    public function xiazais($transSequenceIdn='dfassfaafd',$url=''){
        // 远程文件的URL
        // 本地保存文件的路径
        $localFile = './contractfile/'.$transSequenceIdn.'.zip';
        // 初始化cURL会话
        $ch = curl_init();
        // 设置cURL选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 执行cURL请求
        $fileContent = curl_exec($ch);
        // 关闭cURL会话
        curl_close($ch);
        // 本地保存文件的路径
        // 写入文件
        if (file_put_contents($localFile, $fileContent)) {

            $zip = new ZipArchive();
            $res = $zip->open($localFile);
            if ($res === TRUE) {
                // 尝试创建目录
                if (mkdir('./contractfile/'.$transSequenceIdn.'/', 0777, true)) {
                    $zip->extractTo('./contractfile/'.$transSequenceIdn.'/');
                    $zip->close();
                    // 使用scandir获取目录下的所有项
                    $items = scandir('./contractfile/'.$transSequenceIdn.'/');
                    // 初始化一个变量来存储唯一文件的名称
                    $uniqueFileName = 123;
                    // 遍历项

                    foreach ($items as $item) {
                        // 跳过'.'和'..'
                        if ($item !== '.' && $item !== '..') {
                           dump($item);exit;

                            // 否则，设置唯一文件的名称
                        }
                    }
                    // 检查是否找到了唯一文件$uniqueFileName
                }
            }

        }
        return 'https://'.request()->host().'/contractfile/'.$transSequenceIdn.'/'.$uniqueFileName;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月23月 13:09:13
     * ps:复制模板给平台
     * url:{{URL}}/index.php/api/common/copytemplate
     */
    public function copytemplate(){
        $ids = input('param.ids');

        $template = Db::name('template')->where('id','=',$ids)->find();

        $temdata = $template;

        $temdata['createtime'] = time();
        $temdata['updatetime'] = time();
        $temdata['classify_id'] = 0;
        $temdata['type_id'] = 0;
        $temdata['type'] = '';
        $temdata['id'] = '';
        $templateId = Db::name('template')->insertGetId($temdata);
        //添加模版内容
        $temcontent = Db::name('template_content')->where('template_id','=',$ids)->order('id asc')->select();

        $data = array();
        foreach($temcontent as $k=>$v){
            $data = $v;
            $data['id'] = '';
            $data['template_id'] = $templateId;
            Db::name('template_content')->insertGetId($data);
        }
        return '操作成功';
    }
}
