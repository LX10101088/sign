<?php

namespace app\admin\model;

use think\Model;

class Contract extends Model
{


    // 表名
    protected $name = 'contract';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'expireTime_text'
    ];
    

    



    public function getExpiretimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['expireTime']) ? $data['expireTime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setExpiretimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }



    public function archive()
    {
        return $this->belongsTo('app\admin\model\Archive', 'id', 'contract_id', [], 'LEFT')->setEagerlyType(0);
    }

    public function signing()
    {
        return $this->belongsTo('app\admin\model\Signing', 'id', 'contract_id', [], 'LEFT')->setEagerlyType(0);
    }
}
