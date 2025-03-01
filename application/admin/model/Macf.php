<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Macf extends Model
{


    // 表名
    protected $name = 'contract_macf';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'signTime_text'
    ];
    

    



    public function getSigntimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['signTime']) ? $data['signTime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setSigntimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function contract()
    {
        return $this->belongsTo('app\admin\model\Contract', 'contract_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
