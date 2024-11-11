<?php

namespace app\common\controller;


use app\admin\model\AuthGroupAccess;
use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use app\api\controller\Wxepay;
use fast\Random;
use think\Controller;
use think\Db;


/**
 * 订单公共接口
 */
class Commonorder extends Controller
{


    public function _initialize()
    {


        parent::_initialize();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 13:28:57
     * ps:生成订单
     */
    public function addorder($type,$typeId,$goodsId,$number){


        $goods = Db::name('goods')->where('id','=',$goodsId)->find();
        $data['orderNo'] = $this->orderNo();
        $data['goods_id'] = $goodsId;
        $data['type'] = $type;
        $data['type_id'] = $typeId;
        $data['price'] = $goods['price'];
        $data['paystatus'] = 0;
        $data['state'] = 1;
        $data['createtime'] = time();
        $data['totalprice'] = $goods['price']*$number;
        $data['number'] = $number;
        $orderId = Db::name('order')->insertGetId($data);
        return $orderId;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月11月 15:18:22
     * ps:生成订单编号
     */
    public function orderNo(){
        $prefix = 'ST'; // 订单编号前缀
        $suffix = date('Ymd'); // 当前日期作为后缀
        $randomDigits = $this->generateRandomDigits(6); // 生成6位随机数字

        $orderNumber = $prefix . $suffix . $randomDigits;
        $order = Db::name('order')->where('orderNo','=',$orderNumber)->find();
        if($order){
            $orderNumber = $this->orderNo();
        }
        return $orderNumber;
    }

    function generateRandomDigits($length) {
        $digits = '';
        $chars = '0123456789';

        for ($i = 0; $i < $length; $i++) {
            $digits .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $digits;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 14:31:26
     * ps:微信native支付订单
     */
    public function nativeorder($orderId){
        $wxepay = new Wxepay();
        $order = Db::name('order')->where('id','=',$orderId)->find();
        $goods = Db::name('goods')->where('id','=',$order['goods_id'])->find();
        $data['goodsName'] = $goods['name'];
        $data['orderNo'] = $order['orderNo'];
        $data['price'] = $order['totalprice'];

        $url = $wxepay->nativeorder($data);

        $rest['code'] = 300;

        if($url){
            $common = new Common();
            $qrcode = $common->addqrcode($url);

            $edit['wxurl'] = $qrcode;
            Db::name('order')->where('id','=',$order['id'])->update($edit);
            $rest['code'] = 200;
            $rest['url'] = $qrcode;
        }else{
            $rest['code'] = 300;
        }
        return $rest;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 15:34:06
     * ps:根据订单状态做操作
     */
    public function opeateorder($state,$orderId){
        $order = Db::name('order')->where('id','=',$orderId)->find();
        $goods = Db::name('goods')->where('id','=',$order['goods_id'])->find();
        if($state == 2){
            //订单支付完成
            //操作订单状态
            $ordata['paystatus'] = 1;
            $ordata['state'] = 2;
            $ordata['paytime'] = time();
            $ordata['updatetime'] = time();
            Db::name('order')->where('id','=',$orderId)->update($ordata);
            //添加分佣记录
            $service_id = 0;
            $price = 0;
            if($order['type'] == 'enterprise'){
                $user = Db::name('enterprise')->where('id','=',$order['type_id'])->find();
                $codata['service_id'] = $user['service_id'];
                $service_id = $user['service_id'];
            }else{
                $user = Db::name('custom')->where('id','=',$order['type_id'])->find();
                $codata['service_id'] = $user['service_id'];
                $service_id = $user['service_id'];
            }
            if($service_id){
                $service = Db::name('service')->where('id','=',$service_id)->find();
                $ratio = $service['ratio']/100;
                $price = round($ratio * $order['totalprice'],2);

                $codata['price'] =$price;
            }
            $codata['order_id'] =$orderId;
            $codata['state'] =0;
            $codata['createtime'] =time();
            Db::name('commission')->insertGetId($codata);
            //订单权益分发
            $common = new Common();
            $common->addaccountequity($order['type'],$order['type_id'],$goods['contract'],$goods['template']);
        }else{
            $ordata['state'] = $state;
            $ordata['updatetime'] = time();
            Db::name('order')->where('id','=',$orderId)->update($ordata);

        }
        return true;
    }
}
