<?php

namespace app\index\controller;

use app\api\controller\Aesutil;
use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use app\common\controller\Common;
use app\common\controller\Commonattestation;
use app\common\controller\Commoncontract;
use app\common\controller\Commonenter;
use app\common\controller\Commonorder;
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
class Epaynotify extends Frontend
{


    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    //支付回调
    public function index()
    {

        $getCallBackData = file_get_contents('php://input'); //接收来自微信的回调数据
        $data['value'] = $getCallBackData;
        $data['createtime'] = time();

        Db::name('notify')->insertGetId($data);
        file_put_contents('./callBack.json',$getCallBackData."\n\r",FILE_APPEND); //将接收到的数据存入callBack.json文件中
        $getData = new Aesutil(); //new AesUtil;
        $getReturnData = $getCallBackData;

        $disposeReturnData = json_decode($getReturnData, true);  //将变量由json类型数据转换为数组
        $associatedData = $disposeReturnData['resource']['associated_data'];  //获取associated_data数据,附加数据
        $nonceStr = $disposeReturnData['resource']['nonce'];                  //获取nonce数据,加密使用的随机串
        $ciphertext = $disposeReturnData['resource']['ciphertext'];           //获取ciphertext数据,base64编码后的数据密文
        $result = $getData -> decryptToString($associatedData,$nonceStr,$ciphertext); //调用微信官方给出的方法将解密后的数据赋值给变量


        $array_data = json_decode($result,true);  //将解密后的数据转换为数组

        if($array_data['trade_state'] == "SUCCESS"){
            $order = Db::name('order')->where('orderNo','=',$array_data['out_trade_no'])->find();
            if($order){
                if($order['paystatus'] == 0){
                    $orderdata['wxorderNo'] = $array_data['transaction_id'];
                    $orderdata['paytime'] = time();
                    $orderdata['updatetime'] = time();
                    Db::name('order')->where('id','=',$order['id'])->update($orderdata);
                    $commonorder = new Commonorder();
                    $commonorder->opeateorder($order['id']);
                }
            }
            $res['code'] = 200;
            $res['message'] = '成功';
            echo json_encode($res);
        }
    }



}
