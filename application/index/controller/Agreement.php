<?php

namespace app\index\controller;

use app\api\controller\Fadada;
use app\api\controller\Lovesigning;
use app\common\controller\Common;
use app\common\controller\Commonattestation;
use app\common\controller\Commoncontract;
use app\common\controller\Commonenter;
use app\common\controller\Commonsignature;
use app\common\controller\Commonuser;
use app\common\controller\Frontend;
use think\Db;

/**
 * Created by PhpStorm.
 * User:lang
 * time:2024年11月01月 9:35:55
 * ps:协议展示方法
 */
class Agreement extends Frontend
{


    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月01月 9:36:31
     * ps:用户协议
     */
    public function user()
    {
        $info = Db::name('information')->where('type','=',1)->where('enterprise_id','=',0)->order('id desc')->find();
        //dump($info);exit;
        $this->assign('info',$info);
        return $this->view->fetch();
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月01月 9:36:41
     * ps:隐私政策
     */
    public function privacy()
    {
        $info = Db::name('information')->where('type','=',2)->where('enterprise_id','=',0)->order('id desc')->find();
        $this->assign('info',$info);
        return $this->view->fetch();
    }

}
