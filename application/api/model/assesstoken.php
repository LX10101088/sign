<?php
/**
 * Created by PhpStorm.
 * User: 郎骁
 * Date: 2023年5月31月
 * Time: 15:51
 * PS:话题
 */

namespace app\api\model;


use think\Db;
use think\Model;

class assesstoken   extends  Model
{
    static $table_name = 'assess_token';

    /**
     * Created by PhpStorm.
     * User: lang
     * time:2020年11月17日 11:21:32
     * ps:获取最新assess_token
     * url：
     */
    static   function  getToken($field='*'){

        $rest   = Db::name(self::$table_name)
            ->where('type','=',1)
            ->field($field)
            ->order('id desc')
            ->find();
        $time = time();
        if($rest){
            $times = $time - $rest['createtime'];

        }else{
            $times = 0;
        }
        if($times < 5000){

            return $rest['assess_token'];
        }else{
            return '';
        }

    }
    /**
     * Created by PhpStorm.
     * User: lang
     * time:2020年11月17日 11:21:32
     * ps:获取小程序最新assess_token
     * url：
     */
    static   function  getappletToken($field='*'){

        $rest   = Db::name(self::$table_name)
            ->where('type','=',2)
            ->field($field)
            ->order('id desc')
            ->find();
        $time = time();
        if($rest){
            $times = $time - $rest['createtime'];

        }else{
            $times = 0;
        }
        if($times < 5000){

            return $rest['assess_token'];
        }else{
            return '';
        }

    }
    /**
     * Created by PhpStorm.
     * User: lang
     * time:2020年11月17日 11:21:32
     * ps:获取其他小程序最新assess_token
     * url：
     */
    static   function  getqtappletToken($field='*'){
        $rest   = Db::name(self::$table_name)
            ->where('type','=',5)
            ->field($field)
            ->order('id desc')
            ->find();
        $time = time();
        if($rest){
            $times = $time - $rest['createtime'];

        }else{
            $times = 0;
        }
        if($times < 5000){

            return $rest['assess_token'];
        }else{
            return '';
        }
    }
    /**
     * Created by PhpStorm.
     * User: lang
     * time:2020年11月17日 11:21:32
     * ps:获取最新assess_token
     * url：
     */
    static   function  jsapi_ticket($field='*'){

        $rest   = Db::name(self::$table_name)
            ->where('type','=',3)
            ->field($field)
            ->order('id desc')
            ->find();
        $time = time();
        if($rest){
            $times = $time - $rest['createtime'];

        }else{
            $times = 0;
        }
        if($times < 5000){

            return $rest['assess_token'];
        }else{
            return '';
        }

    }
    /**
     * Created by PhpStorm.
     * User: lang
     * time:2020年11月17日 11:21:32
     * ps:获取最新assess_token
     * url：
     */
    static   function  getTokens($field='*'){

        $rest   = Db::name(self::$table_name)
            ->where('type','=',1)
            ->order('id DESC')
            ->field($field)
            ->find();
        return $rest['assess_token'];



    }
    /**
     * Created by PhpStorm.
     * User: lang
     * time:2020年11月17日 11:20:42
     * ps:添加assessToken
     * url：
     */
    static  function  addToken($data){
        $rest   = Db::name(self::$table_name)->insert($data);
        return $rest;
    }

    /**
     * Created by PhpStorm.
     * User: lang
     * time:2020年11月17日 11:21:32
     * ps:获取其他小程序最新assess_token
     * url：
     */
    static   function  getqtcreateappleturl($field='*'){
        $rest   = Db::name(self::$table_name)
            ->where('type','=',6)
            ->field($field)
            ->order('id desc')
            ->find();
        $time = time();
        if($rest){
            $times = $time - $rest['createtime'];

        }else{
            $times = 0;
        }
        if($times < 1728000){

            return $rest['assess_token'];
        }else{
            return '';
        }
    }

    /**
     * Created by PhpStorm.
     * User: lang
     * time:2020年11月17日 11:21:32
     * ps:获取其他小程序最新assess_token
     * url：
     */
    static   function  getmycreateappleturl($path,$field='*'){
        $rest   = Db::name(self::$table_name)
            ->where('type','=',7)
            ->where('path','=',$path)
            ->field($field)
            ->order('id desc')
            ->find();
        $time = time();
        if($rest){
            $times = $time - $rest['createtime'];

        }else{
            $times = 0;
        }
        if($times < 1728000){

            return $rest['assess_token'];
        }else{
            return '';
        }
    }
}