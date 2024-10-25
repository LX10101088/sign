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
 * time:2024年9月04月 15:22:02
 * ps:爱签回调方法
 */
class Enter extends Frontend
{


    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    //认证回调
    public function replaceenter()
    {
        $ids = $_POST['ids'];

    }


}
