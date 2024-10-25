<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Enterprise extends Model
{


    // 表名
    protected $name = 'enterprise';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'finishedTime_text'
    ];
    

    



    public function getFinishedtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['finishedTime']) ? $data['finishedTime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setFinishedtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
