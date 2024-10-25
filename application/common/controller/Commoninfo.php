<?php

namespace app\common\controller;


use think\Controller;
use think\Db;


/**
 * 查询信息公共接口
 */
class Commoninfo extends Controller
{


    public function _initialize()
    {


        parent::_initialize();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 9:54:53
     * ps:查询服务商信息
     */
    public function getservice($ids,$field='*'){
        $service = Db::name('service')->where('id','=',$ids)->field($field)->find();
        return $service;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 16:38:34
     * ps:获取企业信息
     */
    public function getenter($ids,$field = '*'){
        $enter = Db::name('enterprise')->where('id','=',$ids)->field($field)->find();

        return $enter;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 16:38:34
     * ps:获取个人信息
     */
    public function getcustom($ids,$field = '*'){
        $custom = Db::name('custom')->where('id','=',$ids)->field($field)->find();

        return $custom;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月22月 15:52:31
     * ps:获取模板分类信息
     */
    public function gettemclassify($ids,$field = '*'){
        $template_classify = Db::name('template_classify')->where('id','=',$ids)->field($field)->find();

        return $template_classify;
    }
}
