<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Templatecontent extends Model
{


    // 表名
    protected $name = 'template_content';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];
    

    







}
