<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        return $this->view->fetch();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月22月 16:49:24
     * ps:平台赠送
     * url:{{URL}}/index.php/index/index/gift
     */
    public function gift()
    {
        return $this->view->fetch();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月22月 16:49:24
     * ps:平台赠送ajax
     */
    public function ajax_gift(){
        $phone = $_POST['phone'];
        $inviteCode = $_POST['inviteCode'];
        $custom = Db::name('custom')->where('phone','=',$phone)->find();
        if($custom){
            $account = Db::name('account')->where('type','=','custom')->where('type_id','=',$custom['id'])->find();
            $data['contract'] = $account['contract']+5;
            $data['updatetime'] = time();
            Db::name('account')->where('id','=',$account['id'])->update($data);
        }

        $rest['code'] = 200;
        return $rest;
    }
}
