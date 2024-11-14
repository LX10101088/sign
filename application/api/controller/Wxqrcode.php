<?php
/**
 * Created by PhpStorm.
 * User: 郎骁
 * Date: 2021年6月07日
 * Time: 13:35
 */

namespace app\api\controller;


class Wxqrcode   extends  Common
{
    public $appid     = 'wx9619f41db13a6ecf';
    public $app_secret = 'e0ec83c8a0d7658fcccd871230b74300';
    public   $wxurl="https://api.weixin.qq.com/";

    public   function getqrcodelimit($urlc='wxa/getwxacodeunlimit',$path='pages/waybill_add/waybill_add',$scene){
        $wxLogin = new Wxlogin();

        $token  = $wxLogin->getAccessToken();
        $url=$this->wxurl.$urlc.'?access_token='.$token;
        $postdata['page']  = $path;
        $postdata['scene'] = $scene;
        $postdata['width']  = 1080;
        $postdata['is_hyaline']  = TRUE;

        $imgflow=$this->https_request($url,$postdata,'json');

        $t=time();
        $imgDir = '/public/upload/project/qr/';
        if(!file_exists('.'.$imgDir)){
            mkdir('.'.$imgDir, 0777, true);
        }
        //  is_dir('.'.$imgDir) OR mkdir('.'.$imgDir, 0777, true);

        $filepath =$this->saveimg($imgflow,$imgDir,$imgDir);
        return $filepath;
    }


    function gettoken($cache=0,$times=1,$userid=''){

        $token=cache('token');
        $appid=$this->appid;
        $secret=$this->app_secret;
        if(empty($token)||$cache==1){
            $url=$this->wxurl.'cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
            //$token=file_get_contents($url);
            $token   = $this->https_request($url);
            $json = json_decode($token);
            if(isset($json->errcode)){
                if($json->errcode==41002){
                    if($times>0){
                        --$times;
                        $this->gettoken(1,$times);
                    }else{
                        return false;
                    }
                }
            }

            cache('token',$json->access_token,60*60*1.5);
            $token=cache('token');
        }
        return $token;
    }
    /**
     * Created by PhpStorm.
     * User: wang
     * time:2019年3月21日 9:13:44
     * ps:http请求
     */
    function https_request($url,$data='',$type='',$times=1){
        if($type=='json'){//json $_POST=json_decode(file_get_contents('php://input'), TRUE);
            $headers = array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
            $data=json_encode($data);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($curl);
        curl_close($curl);
        /*if(count($output)<200&&$times<6){
            $this->https_request($url,$data,$type,++$times);

        }*/
        return $output;
    }

    function saveimg($jpg,$imgDir,$imgDir_str){
        $jpg_y  = $this->yuanImg($jpg);
        $t=time().rand(1,999999);

        //生成图片
        $filename=$t.".png";///要生成的图片名字

        if(empty($jpg)||strlen($jpg)<200)
        {
            return false;
        }

        $file = fopen('.'.$imgDir.$filename,"wb");//打开文件准备写入

        fwrite($file,$jpg_y);//写入
        fclose($file);//关闭

        return request()->domain().$imgDir.$filename;

    }

    /**
     * 剪切图片为圆形
     * @param  $picture 图片数据流 比如file_get_contents(imageurl)返回的东东
     * @return 图片数据流
     */
    function yuanImg($picture) {

        $src_img = imagecreatefromstring($picture);
        $w   = imagesx($src_img);
        $h   = imagesy($src_img);
        $w   = min($w, $h);
        $h   = $w;
        $img = imagecreatetruecolor($w, $h);


        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        //$bg = imagecolorallocatealpha($img, 255, 255, 255, 127);

        //白色背景
        $bg = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $bg);
        $r   = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        /**
         * 如果想要直接输出图片，应该先设header。header("Content-Type: image/png; charset=utf-8");
         * 并且去掉缓存区函数
         */
        //获取输出缓存，否则imagepng会把图片输出到浏览器
        ob_start();
        imagepng ( $img );
        imagedestroy($img);
        $contents =  ob_get_contents();
        ob_end_clean();
        return $contents;
    }




}