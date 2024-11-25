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
            if($typeId !=0){
                $sms = new Csms();
                $sms->addtemplate($ids);
            }
//            $account = Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->find();
//            //扣除账户模版份数
//            $acedit['template'] = $account['template'] -1;
//            $acedit['usetemplate'] = $account['usetemplate'] +1;
//            $acedit['updatetime'] = time();
//            Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->update($acedit);
            //发送短信

            $this->wordzpdf($ids);
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


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月21月 15:06:29
     * ps:文档转pdf(模版示例文件转换)
     */
    public function wordzpdf($templateId=19){
        $template = Db::name('template')->where('id','=',$templateId)->find();
        if($template['file']){
            if (strpos($template['filename'], '.pdf') !== false) {
                $data['preview'] = $template['file'];
                Db::name('template')->where('id','=',$templateId)->update($data);
                return true;
            }
// 云市场分配的密钥Id
            $secretId = 'AKIDO8HwR3mWlaS1B37lSqGrqdV5LobaV63xU59A';
// 云市场分配的密钥Key
            $secretKey = 'IFTIw656KD4BSqS1t16g2z7LCYQmM162knQ0DYO';
            $source = 'market';
// 签名
            $datetime = gmdate('D, d M Y H:i:s T');
            $signStr = sprintf("x-date: %s\nx-source: %s", $datetime, $source);
            $sign = base64_encode(hash_hmac('sha1', $signStr, $secretKey, true));
            $auth = sprintf('hmac id="%s", algorithm="hmac-sha1", headers="x-date x-source", signature="%s"', $secretId, $sign);
// 请求方法
            $method = 'POST';
// 请求头
            $headers = array(
                'X-Source' => $source,
                'X-Date' => $datetime,
                'Authorization' => $auth,
            );
// 查询参数
            $queryParams = array (
            );
// body参数（POST方法下）
            $bodyParams = array (
                'callbackUrl' => '',
                'fileName' => $template['filename'],
                'url' => $template['file'],
            );
// url参数拼接
            $url = 'http://service-9jiloqcv-1258478321.sh.apigw.tencentcs.com/release/fileConvert';
            if (count($queryParams) > 0) {
                $url .= '?' . http_build_query($queryParams);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function ($v, $k) {
                return $k . ': ' . $v;
            }, array_values($headers), array_keys($headers)));
            if (in_array($method, array('POST', 'PUT', 'PATCH'), true)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($bodyParams));
            }
            $data = curl_exec($ch);

            $data_json = json_decode($data,true);
            if($data_json['code'] == 0){
                $res = $this->xiazchuz($data_json['data']['pdfUrl'],$template['id'].'_'.$template['name']);
                $datas['preview'] = $res;
                Db::name('template')->where('id','=',$templateId)->update($datas);
            }
            curl_close($ch);
        }

        return true;
    }

    public function xiazchuz($imageUrl='',$name=''){
        // 要保存图片的本地路径和文件名
        $localPath = 'template/'.$name.'.pdf';
        // 尝试获取图片内容
        $imageContent = file_get_contents($imageUrl);
        if ($imageContent !== false) {
            // 尝试将图片内容写入到本地文件
            if (file_put_contents($localPath, $imageContent)) {
                return  request()->domain().'/'.$localPath;

            } else {
                return '';
            }
        } else {
            return '';
        }
    }
}
