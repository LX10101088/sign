<?php

namespace app\common\controller;


use app\api\controller\Csms;
use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use think\Controller;
use think\Db;


/**
 * 模版公共方法
 */
class Commontemplate extends Controller
{

    public function _initialize()
    {
        parent::_initialize();
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 13:49:21
     * ps:操作模版信息
     */
    public function operatetemplate($data,$type,$typeId,$ids=null){

        $data['type'] = $type;
        $data['type_id'] = $typeId;
        if($ids){
            $data['updatetime'] = time();
            Db::name('template')->where('id','=',$ids)->update($data);
        }else{
            $data['createtime'] = time();
            $ids = Db::name('template')->insertGetId($data);
            $account = Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->find();
            //扣除账户合同份数
            $acedit['template'] = $account['template'] -1;
            $acedit['usetemplate'] = $account['usetemplate'] +1;
            $acedit['updatetime'] = time();
            Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->update($acedit);
            //发送短信
            $sms = new Csms();
            $sms->addtemplate($ids);
        }

        return $ids;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 13:49:21
     * ps:操作模版信息
     */
    public function operatecontent($data,$ids=null){

        if($ids){
            $data['updatetime'] = time();
            Db::name('template_content')->where('id','=',$ids)->update($data);
        }else{
            $data['createtime'] = time();

            $ids =  Db::name('template_content')->insertGetId($data);
        }

        return $ids;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月24月 15:24:53
     * ps:获取模板链接
     */
    public function gettemplateurl($ids){
        $template = Db::name('template')->where('id','=',$ids)->find();
        $fadada = new Fadada();
        $res = $fadada->gettemplateurl($template['templateNo']);
        return $res;
    }
}
