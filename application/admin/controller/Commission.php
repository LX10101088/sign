<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Commonservice;
use think\Db;
use think\exception\DbException;
use think\response\Json;

/**
 * 订单分佣管理
 *
 * @icon fa fa-circle-o
 */
class Commission extends Backend
{

    protected $noNeedLogin = ['confirm','cancel'];

    /**
     * Commission模型对象
     * @var \app\admin\model\Commission
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Commission;

    }
    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->with(['service','order'])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        foreach($list as $k=>$v){
            if($v['state'] == 0){
                $list[$k]['state'] = '未分佣';
            }else if($v['state'] == 1){
                $list[$k]['state'] = '已分佣';
            }else{
                $list[$k]['state'] = '不分佣';
            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月30月 9:54:15
     * ps:确认分佣
     */
    public function confirm($ids = null){
        $data['state'] = 1;
        $data['updatetime'] = time();
        Db::name('commission')->where('id','=',$ids)->update($data);
        $commonservice = new Commonservice();
        $commonservice->confirmcommission($ids);
        $this->success();
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月30月 9:54:15
     * ps:确认分佣
     */
    public function cancel($ids = null){
        $data['state'] = 2;
        $data['updatetime'] = time();
        Db::name('commission')->where('id','=',$ids)->update($data);
        $this->success();
    }
}
