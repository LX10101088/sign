<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Goods extends Backend
{

    protected $noNeedLogin = ['servicepackage'];


    /**
     * Goods模型对象
     * @var \app\admin\model\Goods
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Goods;

    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 10:45:44
     * ps:服务套餐
     */
    public function servicepackage(){
        $goods = Db::name('goods')->where('deletetime','=',0)->order('id desc')->select();
        $this->assign('goods',$goods);
        return $this->view->fetch();
    }



}
