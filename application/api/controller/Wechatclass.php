<?php
namespace app\api\controller;



use app\api\model\assesstoken;

class Wechatclass{
    public $AppID;
    public $AppSecret;
    public $redirect_uri;
    public $DbSy;
    public $dump_url;
    public $scope;
    public function __construct()
    {

        $this->AppID = "wxec525039a8cfee33";
        $this->AppSecret = "5d1ee1244730caad9ea060ec5383ff53";
    }
    public function getSignPackage($url) {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
//        $url = "{$protocol}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
//        $url = "http://mai.obsend.com/h5";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string);
        // var_dump($signature);die;
        $signPackage = array(
            "appId"     => $this->AppID,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }
    public function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        //echo $_SERVER['DOCUMENT_ROOT'];
//        $data = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/static/yejuzhi/js/access_token.json"));
        // var_dump($data->expire_time);die;
        $assessToken = new assesstoken();
        $ticket = $assessToken::jsapi_ticket();

        if (empty($ticket)) {
            $Wx = new Wxlogin();
            $accessToken = $Wx->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token={$accessToken}";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data['assess_token'] = $ticket;
                $data['type'] = 3;
                $data['createtime'] = time();
                $assessToken::addToken($data);

            }
        }

        return $ticket;
    }

    public function httpGet($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }


}