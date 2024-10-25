<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Commission extends Model
{



    // 表名
    protected $name = 'commission';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];


    public function service()
    {
        return $this->belongsTo('app\admin\model\Service', 'service_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function order()
    {
        return $this->belongsTo('app\admin\model\Order', 'order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }





}
