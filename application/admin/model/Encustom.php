<?php

namespace app\admin\model;

use think\Model;

class Encustom extends Model
{

    // 表名
    protected $name = 'enterprise_custom';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];





    public function custom()
    {
        return $this->belongsTo('app\admin\model\Custom', 'custom_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }




}
