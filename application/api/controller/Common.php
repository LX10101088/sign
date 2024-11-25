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
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Settings;

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
        $info = $file->validate(['seize' => 2097152, 'ext' => 'jpg,png,gif,jpeg,pdf,doc,docx,xlsx,xls'])->move('./' . $path);
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
        $info = Db::name('information')->where('type','=',$type)->where('enterprise_id','=',0)->order('id desc')->find();
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
        $common->getapienter(73);

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
        $taskdetail = $fadada->getcontenttaskdetail($contract['taskId']);
        if($taskdetail['code']==200){
            $docId = $taskdetail['docId'];
        }
        $fillData = array();
        foreach($cotecontent as $k=>$v){
            $fillData[$k]['docId'] =$docId;
            $fillData[$k]['fieldName'] = $v['name'];
            $fillData[$k]['fieldValue'] = $v['content'];
        }
        $rest = $fadada->fillvalues($contract['taskId'],$fillData);
        dump($rest);
        $start = $fadada->startfill($contract['taskId']);
        dump($start);exit;
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

    public function delmrn(){
        $mrn = Db::name('mrn')->where('id','>',1)->order('id desc')->limit(1,4000)->select();
        dump($mrn);exit;


        $mrn = Db::name('mrn')->where('createtime','>',1)->select();
        foreach($mrn as $k=>$v){
            $data['jzys'] = '尚红英';

            Db::name('mrn')->where('id','=',$v['id'])->update($data);
        }

        return '操作完成';
    }

    public function nanxm(){
        $id = rand(1,2980);
        $res = Db::name('andm')->where('id','=',$id)->find();
        return $res['name'];
    }

    public function xsm(){
        $id = rand(1,521);
        $bjx = Db::name('abjx')->where('id','=',$id)->find();
        $id = rand(1,474);
        $nsm = Db::name('ansm')->where('id','=',$id)->find();
        return $bjx['bjx'].$nsm['nsm'];
    }
    function getRandomTimestampBetweenHours($startHour = 8, $endHour = 19) {

        return rand(8,19).':'.rand(0,59).":".rand(0,59);
    }
    function generateCaseData($startDate, $endDate, $maxCasesPerDay) {
        $cases = [];
        $startDateTimestamp = strtotime($startDate);
        $endDateTimestamp = strtotime($endDate);
        $currentTimestamp = $startDateTimestamp;
        $dailyCaseCount = []; // 用于跟踪每天的病例数的数组
        $a = 0;
        $b = 0;
        while ($currentTimestamp <= $endDateTimestamp) {
            $currentDate = date('Y-m-d', $currentTimestamp);
            $currentDayOfWeek = date('w', $currentTimestamp); // 获取当前是星期几
            // 跳过周一和周五
            if ($currentDayOfWeek == 1 || $currentDayOfWeek == 5 || $currentDayOfWeek == 0 || $currentDayOfWeek == 6) {
                $currentTimestamp = strtotime('+1 day', $currentTimestamp);
                continue;
            }
            $jjrDay = $this->qcjjr($currentTimestamp);
            if($jjrDay){
                $currentTimestamp = strtotime('+1 day', $currentTimestamp);
                continue;
            }
            $maxCasesPerDay = rand(0,30);

            $mrn = Db::name('mrn')->where('id','>',10028)->order('id desc')->limit(4000)->select();


            for ($i = 0; $i < $maxCasesPerDay; $i++) {
                // 生成随机的初诊时间（在同一天内的秒数），然后加上当天的起始时间戳

                $initialDiagnosisTime =date('Y-m-d H:i:s',strtotime($currentDate.' '.$this->getRandomTimestampBetweenHours()));
                // 生成其他病例信息
                //$gender = ['男', '女'][array_rand([0, 1])];
//                $age = $this->generateRandomAge();
//                $phoneNumber = $this->generateRandomPhoneNumber();
//
//                if($gender == '男'){
//                    $firstName = $this->nanxm();
//                }else{
//                    $firstName = $this->xsm();
//                }
                // 生成病例号
//                $caseNumber = $this->generateCaseNumber($currentDate, $dailyCaseCount);
//                $noSpacesString = str_replace(' ', '', $firstName);


                // 将病例信息添加到数组中

                $a = rand(0,3999);
                if($b){
                    if($a == $b){
                        $a = rand(0,4000);

                    }
                }
                $cases[] = [
//                    'mrnone' => $caseNumber,

                    'mrntwo' => $mrn[$a]['mrntwo'],
                    'name' =>$mrn[$a]['name'],
                    'sex' =>$mrn[$a]['sex'],
                    'age' => $mrn[$a]['age'],
                    'createtime' => strtotime($initialDiagnosisTime),
                    'phone' => $mrn[$a]['phone'],
//                'mrntwo' => '9999'. mt_rand(10000, 99999),
//                'name' =>$noSpacesString,
//                'sex' => $gender,
//                'age' => $age,
//                'createtime' => strtotime($initialDiagnosisTime),
//                'phone' => $phoneNumber
                ];

                $b = $a;

                // 如果达到了每天设置的最大病例数（理论上这里不会超出，因为我们在循环中控制了数量）
                // 但为了安全起见，我们还是检查一下是否超出了maxCasesPerDay
                if (count(array_filter($cases, function($case) use ($currentDate) {
                        return date('Y-m-d',$case['createtime']) === $currentDate;
                    })) > $maxCasesPerDay) {
                    break; // 实际上这里应该不会执行到，因为我们在外层循环已经限制了数量
                }
            }

            // 移动到下一天（即使内层循环因为达到最大病例数而提前退出）
            $currentTimestamp = strtotime('+1 day', $currentTimestamp);
        }

        return $cases;
    }

    public function qcjjr($time){
        $jjr = Db::name('ajjr')->where('jjr','=',$time)->find();
        if($jjr){
            return 1;
        }else{
            return 0;
        }
    }

    function generateRandomPhoneNumber() {
        // 定义手机号的开头部分，这里只列举了常见的几个，可以根据需要扩展
        $prefixes = [
            // 中国联通号段
            '130', '131', '132',
            '145', // 联通3G无线上网卡专属号段
            '155', '156',
            '166',
            '175', '176',
            '185', '186',
            // 注意：196也被视为联通号段，但在此示例中未列出，可根据需要添加

            // 中国电信号段
            '133',
            '149', // 电信物联网号段，有时也作为普通手机号段使用
            '153',
            '173',
            '177',
            '180', '181', '189',
            '191', '199', // 电信新推出的号段

            // 中国移动号段
            '134', '135', '136', '137', '138', '139',
            '147', // 移动数据卡号段
            '150', '151', '152', '157', '158', '159',
            '172', // 移动物联网号段
            '178',
            '182', '183', '184', '187', '188',
            '198', // 移动新号段
        ];

        // 随机选择一个前缀
        $prefix = $prefixes[array_rand($prefixes)];

        // 生成9位随机数字
        $randomDigits = mt_rand(100000000, 999999999); // 注意这里生成的数字是10位，但只需要后9位
        $randomDigits = str_pad(substr($randomDigits, -9), 9, '0', STR_PAD_LEFT); // 确保是9位，前面补0

        // 组合成完整的手机号
        $phoneNumber = $prefix . $randomDigits;
        $shortenedString = substr($phoneNumber, 0, 11);

        return $shortenedString;
    }


    public function tesss(){
        // 生成数据
        $caseData = $this->generateCaseData('2023-1-1', '2024-9-30', 5); // 生成3天，每天5个病例的数据
        Db::name('mrn')->insertAll($caseData);
        dump(count($caseData));exit;
        return '操作成功';
// 输出数据（这里输出所有病例以验证）
//        foreach ($caseData as $case) {
//            echo "病例号1: {$case['病例号1']},病例号2: {$case['病例号2']}, 姓名: {$case['姓名']}, 性别: {$case['性别']}, 年龄: {$case['年龄']}, 初诊时间: {$case['初诊时间']}, 手机号: {$case['手机号']}\n";
//        }
    }

    public function delmrnss(){
        $mrn = Db::name('mrn')->where('createtime','>',1)->select();
        foreach($mrn as $k=>$v){
            $data['jzys'] = '尚红英';

            Db::name('mrn')->where('id','=',$v['id'])->update($data);
        }

        return '操作完成';
    }

    public function delmrn1(){
        $a = 0;
        for($i=10000;$i<=27377;$i++){
            $mrn = Db::name('mrn')->where('id','=',$i)->find();
            if($mrn){
                $res = Db::name('mrn')->where('createtime','=',$mrn['createtime'])->where('id','<>',$mrn['id'])->delete();
                if($res){
                    $a+=1;
                }
//                foreach($res as $k=>$v){
//                    $data['createtime'] = $mrn['createtime']+rand(3600, 18000);
//                    Db::name('mrn')->where('id','=',$v['id'])->update($data);
//                }
//                $a += count($res);
            }
        }
        dump($a);exit;

    }

    public function delmrn2(){
        $a = 0;
        for($i=1;$i<=2;$i++) {
            $id = rand(1, 27388);
            $mrn = Db::name('mrn')->where('id', '=', $id)->delete();
            if($mrn){
                $a+=1;
            }
        }
        dump($a);exit;
    }
    public function delmrn3(){
        $a = 0;
        for($i=1;$i<=5000;$i++) {
            $id = rand(1, 27375);
            $yzmrn = Db::name('mrn')->where('id', '=', $id)->find();
            if($yzmrn){
                $data['phone'] = '';
                $mrn = Db::name('mrn')->where('id', '=', $id)->update($data);
                if($mrn){
                    $a+=1;
                }
            }

        }
        dump($a);exit;
    }
    public function delmrn4(){
        $mrn = Db::name('mrn')->where('id','<>',0)->order('createtime desc')->select();
        foreach($mrn as $k=>$v){
            $date = $this->isWithinSpecifiedTimeRanges($v['createtime']);
            $data['createtime'] = $date;
            Db::name('mrn')->where('id','=',$v['id'])->update($data);
        }
        return '操作完成';
    }
    function isWithinSpecifiedTimeRanges($timestamp) {

        // 创建给定的时间戳对应的 DateTime 对象
        // 获取给定时间戳所属日期的起始时间戳（当天的午夜 00:00:00）

        // 计算时间段的起始和结束秒数（从当天午夜开始计算）
        $morningStart =strtotime(date('Y-m-d',$timestamp).' '.'8:40:00');// 8:40 AM

        $morningEnd = strtotime(date('Y-m-d',$timestamp).' '.'12:00:00'); // 12:00 PM
        $afternoonStart = strtotime(date('Y-m-d',$timestamp).' '.'13:00:00'); // 1:00 PM
        $afternoonEnd =  strtotime(date('Y-m-d',$timestamp).' '.'15:00:00');  // 5:00 PM

        // 检查给定的时间戳是否在上午 8:40 至中午 12:00 之间或下午 13:00 至 17:00 之间
        if (($timestamp >=  $morningStart && $timestamp <  $morningEnd) ||
            ($timestamp >=  $afternoonStart && $timestamp <  $afternoonEnd)) {

            // 如果在给定的时间段内，则直接返回原始时间戳（或对应的 DateTime 对象）
            return $timestamp; // 或者 return clone $givenTime;
        } else {

            // 如果不在指定的时间段内，则生成一个随机时间
            $isMorning = rand(0, 1); // 随机选择上午或下午
            $date = date('Y-m-d',$timestamp);
            if($isMorning == 1){
                //下午
                $newdate = $date.' '.rand(13,16).':'.rand(0,59).':'.rand(0,59);
            }else{
                $newdate = $date.' '.rand(8,11).':'.rand(0,59).':'.rand(0,59);

            }

            // 返回随机时间的时间戳
            return strtotime($newdate);
        }
    }


    public function delmrn5(){
        $a = 0;
        $mrn = Db::name('mrn')->where('id','>',1)->order('createtime desc')->select();
        foreach($mrn as $k=>$v){
            $startTime = $v['createtime'] - (10 * 60); // 当前时间减去 10 分钟
            $endTime = $v['createtime'] + (10 * 60); // 当前时间加上 10 分钟
            $mrns = Db::name('mrn')
                ->where('id','<>',$v['id'])
                ->where('createtime', '>', $startTime)
                ->where('createtime', '<', $endTime)
                ->select();
            if($mrns){
                foreach($mrns as $kk=>$vv){
                    $time = rand(10,100)*60;

                    $gender =array_rand([0, 1]);
                    if($gender == 1){
                        $data['createtime'] = (int)$vv['createtime']+$time;

                    }else{
                        $data['createtime'] = (int)$vv['createtime']-$time;

                    }

                    //$data['createtime'] = $this->isWithinSpecifiedTimeRanges($datacreatetime);
                    Db::name('mrn')->where('id','=',$vv['id'])->update($data);
                }
                $a+=1;
            }
        }
        dump($a);exit;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月11月 15:19:21
     * ps:微信分享
     * url:{{URL}}/index.php/api/common/wxshare
     */
    public function wxshare(){
        $url = input('param.url');

        $wx = new Wechatclass();
        $sign = $wx->getSignPackage($url);
        ajaxReturn(['code'=>200,'msg'=>'获取成功','data'=>$sign]);

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月19月 9:47:24
     * ps:生成小程序二维码
     * url:{{URL}}/index.php/api/common/appleterwm
     */
    public function appleterwm(){
        $path = input('param.path');
        $scene = input('param.scene');
        $version = input('param.version');

        $wxqrcode = new Wxqrcode();
        $url = $wxqrcode->getqrcodelimit('wxa/getwxacodeunlimit',$path,$scene,$version);
        if($url){
            ajaxReturn(['code'=>200,'msg'=>'生成成功','url'=>$url]);
        }else{
            ajaxReturn(['code'=>300,'msg'=>'二维码生成失败，请稍后重试']);
        }
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月20月 13:26:28
     * ps:word转html操作
     * url:{{URL}}/index.php/api/common/cwth
     */
    public function cwth(){


        // Word文档的路径
        $docPath = 'contract/委托合同.docx'; // 替换为你的Word文档的实际路径
        // 加载Word文档
        try {
            $phpWord = IOFactory::load($docPath, 'Word2007'); // 对于.docx文件使用'Word2007'
        } catch (\Exception $e) {
            die('无法加载Word文档: ' . $e->getMessage());
        }
        // 创建一个临时文件来保存HTML输出，或者你可以直接输出到浏览器
        $tempHtmlFile = 'temp_output.html';
        // 保存为HTML格式，并尝试保留样式
        $objWriter = IOFactory::createWriter($phpWord, 'HTML');
        $objWriter->save($tempHtmlFile);
        // 读取生成的HTML文件内容
        $htmlContent = file_get_contents($tempHtmlFile);
        //$cleanedHtmlContent = str_replace(["\t", "\n","\r"], '', $htmlContent);

        ajaxReturn(['code'=>200,'msg'=>'获取成功','content'=>$htmlContent]);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月20月 15:38:49
     * ps:随机删除mrnprice表
     * url:{{URL}}/index.php/api/common/sjdelmrnprice
     */
    public function sjdelmrnprice(){

        $a1 = strtotime('2017-12-30 00:00:01');
        $a2 = strtotime('2017-1-1 00:00:01');
//        dump('结束时间：'.$a1);
//
//        dump('开始时间：'.$a2);exit;
        $startTime = '1483200001';
        $endTime = '1514563201';
        $mrn = Db::name('mrn_price')
            ->where('id','<>',0)
            ->select();
        dump(count($mrn));exit;
        shuffle($mrn);
        $b = 0;
        foreach($mrn as $k=>$v){
            $del = array_rand([0,1]);
            if($del == 1){
                Db::name('mrn_price')->where('id','=',$v['id'])->delete();
                $b+=1;
            }
            if($b == 203){
                dump('删除完成');exit;
            }
        }
        dump('循环完成，未删除完成');

    }

    public function editmrnprice(){

        $mrn = Db::name('mrn_price')
            ->where('id','<>',0)
            ->select();


        foreach($mrn as $k=>$v){
            $arra = array_rand([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]);

            if($arra == 1){
                $number = rand(20,5000).'.00';
            }else if($arra == 2){
                $randomInt = mt_rand(200 * 100, 5000 * 100); // 生成2000到500000之间的随机整数
                $randomFloat = $randomInt / 100;            // 得到两位小数的浮点数
                $number =  sprintf("%.1f", $randomFloat).'0';         // 格式化输出为两位小数
            }else if($arra == 3){
                $number = rand(200,2200).'.00';
            }else if($arra == 4){
                $randomInt = mt_rand(200 * 100, 2200 * 100); // 生成2000到500000之间的随机整数
                $randomFloat = $randomInt / 100;            // 得到两位小数的浮点数
                $number =  sprintf("%.1f", $randomFloat).'0';         // 格式化输出为两位小数
            }else if($arra == 5){
                $number = rand(200,2200).'.00';
            }else if($arra == 6){
                $randomInt = mt_rand(200 * 10, 2200 * 10); // 生成2000到500000之间的随机整数
                $randomFloat = $randomInt / 100;            // 得到两位小数的浮点数
                $number =  sprintf("%.1f", $randomFloat).'0';         // 格式化输出为两位小数
            }else if($arra == 7){
                $number = rand(200,2200).'.00';
            }else if($arra == 8){
                $randomInt = mt_rand(200 * 10, 2200 * 10); // 生成2000到500000之间的随机整数
                $randomFloat = $randomInt / 100;            // 得到两位小数的浮点数
                $number =  sprintf("%.1f", $randomFloat).'0';         // 格式化输出为两位小数
            }else if($arra == 9){
                $number = rand(20,253).'.00';
            }else if($arra == 10){
                $randomInt = mt_rand(200 * 10, 800 * 10); // 生成2000到500000之间的随机整数
                $randomFloat = $randomInt / 100;            // 得到两位小数的浮点数
                $number =  sprintf("%.1f", $randomFloat).'0';         // 格式化输出为两位小数
            }else if($arra == 11){
                $number = rand(20,150).'.00';
            }else{
                $number = rand(20,100).'.00';

            }
            $formattedNumber = number_format($number, 2, '.', ',');
            $data['price'] = $formattedNumber;
            Db::name('mrn_price')->where('id','=',$v['id'])->update($data);
        }
        $mrn = Db::name('mrn_price')
            ->where('id','<>',0)
            ->select();
        $num = 0;
        foreach($mrn as $k=>$v){
            $num +=str_replace(',', '', $v['price']);
        }
        dump($num);exit;
        dump('操作完成');
    }

    public function editmrnprice2(){

        $mrn = Db::name('mrn_price')
            ->where('id','<>',0)
            ->select();

        $previousPrices = array_fill(0, 3, null); // 用于存储最近三次的价格，初始化为null

        foreach ($mrn as $k => $v) {
            $rand  = rand(1,30);

            $attempt = 0;
            do {

                $price = $this->sjis($rand);
                $attempt++;

                if (in_array($price, $previousPrices)) {

                    continue;
                }

                $data['price'] = $price;
                array_shift($previousPrices);
                array_push($previousPrices, $price);
                break;
            } while ($attempt < PHP_INT_MAX && in_array($price, $previousPrices));

            Db::name('mrn_price')->where('id', '=', $v['id'])->update($data);

            //$attempt = 0;
        }
        $mrn = Db::name('mrn_price')
            ->where('id','<>',0)
            ->select();
        $num = 0;
        foreach($mrn as $k=>$v){
            $num += $v['price'];
        }
        dump($num);exit;
        dump('操作完成');
    }

    //随机数
    public function sjis($rand){
        $fruits1 = array("100", "110", "120", "130", "140", "150","165","170","1600", "1560", "1520", "1480", "1400", "1380","1280","1260","1200","1180","1140","1100","1080","1040","1000","998","980","970","960","950","940","920","910","900","890","880","870","860","850","840","830","820","810","800","510","520","530", "540", "550", "560", "580", "590","600","620","630", "640", "650", "660", "670", "680","690","700","710", "720", "740", "750", "760", "770","780","790","798");
        $fruits4 = array("20", "30", "40", "60", "70", "80", "90","100", "110", "120", "130", "140", "150","165","170","180", "190", "200", "210", "220", "230","240","250","260", "270", "280", "290", "300", "320","340","350","360", "370", "380", "390", "400", "410","420","430","450", "460", "470", "480", "490", "500");
        $fruits5 = array("20", "30", "40", "60", "70", "80", "90","100", "110", "120", "130", "140", "150","165","170","180", "190", "200");

        $fruits2 = array(  "1620", "1680", "1700", "1780", "1996");
        $fruits3 = array(  "2200", "2680", "2994", "3500", "3980", "3992", "4000", "4280", "4300", "4400", "4280", "4300", "4400", "4500", "4550", "4600", "4680", "4700", "4790", "4800", "4900", "5000");

        switch ($rand){
            case 1:
                $fruits = $fruits1;
                break;
            case 2:
                $fruits = $fruits4;
                break;
            case 3:
                $fruits = $fruits4;
                break;
            case 4:
                $fruits = $fruits4;
                break;
            case 5:
                $fruits = $fruits3;
                break;
            case 6:
                $fruits = $fruits2;
                break;
            case 7:
                $fruits = $fruits5;
                break;
            case 8:
                $fruits = $fruits4;
                break;
            case 9:
                $fruits = $fruits5;
                break;
            case 10:
                $fruits = $fruits4;
                break;
            case 11:
                $fruits = $fruits5;
                break;
            case 12:
                $fruits = $fruits4;
                break;
            case 13:
                $fruits = $fruits5;
                break;
            case 15:
                $fruits = $fruits4;
                break;
            case 14:
                $fruits = $fruits5;
                break;
            case 16:
                $fruits = $fruits4;
                break;
            case 17:
                $fruits = $fruits5;
                break;
            case 18:
                $fruits = $fruits4;
                break;
            case 19:
                $fruits = $fruits5;
                break;
            case 20:
                $fruits = $fruits4;
                break;
            case 21:
                $fruits = $fruits5;
                break;
            case 22:
                $fruits = $fruits4;
                break;
            case 23:
                $fruits = $fruits5;
                break;
            case 24:
                $fruits = $fruits4;
                break;
            case 25:
                $fruits = $fruits5;
                break;
            case 26:
                $fruits = $fruits4;
                break;
            case 27:
                $fruits = $fruits5;
                break;
            case 28:
                $fruits = $fruits4;
                break;
            case 29:
                $fruits = $fruits5;
                break;
            case 30:
                $fruits = $fruits4;
                break;
        }

        $randomKey = array_rand($fruits);
        $randomFruit = $fruits[$randomKey];
        return $randomFruit;
    }





}
