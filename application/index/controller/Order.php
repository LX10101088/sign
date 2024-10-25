<?php

namespace app\index\controller;


use app\common\controller\Frontend;
use think\Db;

/**
 * Created by PhpStorm.
 * User:lang
 * time:2024年9月04月 15:22:02
 * ps:法大大回调方法
 */
class Order extends Frontend
{


    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function addorder(){
        $post = $_POST;
        dump($post);exit;
    }


}
