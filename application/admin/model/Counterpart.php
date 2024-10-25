<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Counterpart extends Model
{

    // 表名
    protected $name = 'counterpart';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];




    public function enter()
    {
        return $this->belongsTo('app\admin\model\Enterprise', 'type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function custom()
    {
        return $this->belongsTo('app\admin\model\Custom', 'type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }




}
