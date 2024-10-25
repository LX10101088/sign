<?php
/**
 * Ajax方式返回数据到客户端
 * @author maisymind
 */
function ajaxReturn($data,$type='') {
    if(empty($type)) $type  =  "JSON";
    switch (strtoupper($type)){
        case 'JSON' :
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data));
        case 'XML'  :
            // 返回xml格式数据
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($data));
        case 'JSONP':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
            exit($handler.'('.json_encode($data).');');
        case 'EVAL' :
            // 返回可执行的js脚本
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
    }
}


/**
 * 接口返回成功信息
 */
function returnSuccess($msg,$code,$data = array()){
    $result['code'] = $code;
    $result['msg'] = $msg;
    $result['data'] = $data;
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($result));
}