<?php

namespace app\admin\controller;

use app\api\controller\Fadada;
use app\common\controller\Backend;
use app\common\controller\Common;
use app\common\controller\Commoninfo;
use app\common\controller\Commonsignature;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 印章管理
 *
 * @icon fa fa-circle-o
 */
class Signature extends Backend
{

    /**
     * Signature模型对象
     * @var \app\admin\model\Signature
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Signature;

    }
    public function import()
    {
        parent::import();
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
        if($this->auth->usertype == 'custom'){
            $enterId = $this->getenter();
            $list = $this->model
                ->where($where)
                ->where('deletetime','=',0)
                ->where('type','=','enterprise')
                ->where('type_id','=',$enterId)
                ->where('deletetime','=',0)
                ->order($sort, $order)
                ->paginate($limit);
        }elseif($this->auth->usertype == 'service'){
            $list = $this->model
                ->where($where)
                ->where('deletetime','=',0)
                ->order($sort, $order)
                ->paginate($limit);
        }else{
            $list = $this->model
                ->where($where)
                ->where('deletetime','=',0)
                ->order($sort, $order)
                ->paginate($limit);
        }
        $common = new Commoninfo();
        foreach($list as $k=>$v){
            if($v['type'] == 'enterprise'){
                $list[$k]['type'] = '企业';
                $list[$k]['type_id'] =$common->getenter($v['type_id'])['name'];
            }else if($v['type'] == 'custom'){
                $list[$k]['type'] = '个人';
               $list[$k]['type_id'] =$common->getcustom($v['type_id'])['name'];
            }
            if($v['state'] == 0){
                $list[$k]['state'] = '制作中';
            }else if($v['state'] == 1){
                $list[$k]['state'] = '启用';
            }else if($v['state'] == 2){
                $list[$k]['state'] = '停用';
            }else if($v['state'] == 3){
                $list[$k]['state'] = '作废';
            }
            if($v['default'] == 0){
                $list[$k]['default'] = '否';
            }else if($v['default'] == 1){
                $list[$k]['default'] = '是';
            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {

        $enterId = $this->getenter();

        $commonsignature = new Commonsignature();
        $res = $commonsignature->addplatformsignature($enterId,'enterprise');

        if($res['code'] == 300){
            $this->error($res['msg']);
        }
        $this->assign('url',$res['hwBoardUrl']);
        return $this->view->fetch();

    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 9:13:36
     * ps:设置默认
     */
    public function setdefault($ids=null){
        $commonsignature = new Commonsignature();
        $commonsignature->setdefault($ids);
        $this->success('操作成功');
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月06月 9:05:26
     * ps:查询状态
     */
    public function getstate($ids=null){
        $commonsignature = new Commonsignature();
        $commonsignature->getplatformsignature($ids);
        $this->success('操作成功');
    }

    /**
     * 删除
     *
     * @param $ids
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        Db::startTrans();
        try {
            $commonsignature =new Commonsignature();
            foreach ($list as $item) {
                $commonsignature->delsignature($item['id']);
                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }
}
