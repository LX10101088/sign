<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Contractarchive extends Model
{



    // 表名
    protected $name = 'contract_archive';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];




    public function contract()
    {
        return $this->belongsTo('app\admin\model\Contract', 'contract_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }





}
