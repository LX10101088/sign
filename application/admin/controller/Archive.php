<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Common;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 
 *
 * @icon fa fa-archive
 */
class Archive extends Backend
{

    /**
     * Archive模型对象
     * @var \app\admin\model\Archive
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Archive;

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
                ->order($sort, $order)
                ->paginate($limit);
        }elseif($this->auth->usertype == 'service'){
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
        }else{
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
        }

        $common = new Common();
        foreach($list as $k=>$v){
            if($v['pid']){
                $list[$k]['pid'] =$common->getarchive($v['pid'],'name')['name'];
            }else{
                $list[$k]['pid'] = '-';
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
        if (false === $this->request->isPost()) {
            $enterId = $this->getenter();
            $this->assign('enterId',$enterId);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $enterId = $this->getenter();
            if($enterId){
                $params['type'] = 'enterprise';
                $params['type_id'] = $enterId;
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }
    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            $enterId = $this->getenter();
            $this->assign('enterId',$enterId);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月25月 15:24:15
     * ps:批量归档合同
     */
    public function batch($ids=null,$type){
        $enterId = $this->getenter();

        if (false === $this->request->isPost()) {
            $this->assign('enterId',$enterId);
            return $this->view->fetch();
        }
        $row = $this->request->post('row/a');

        $arr = explode(',',$ids);
        foreach($arr as $k=>$v){
            if($v){
                $data['type'] = 'enterprise';
                $data['type_id'] =$enterId;
                $data['archive_id'] = $row['pid'];
                $data['contract_id'] = $v;
                $data['attribute'] = $type;
                $data['notes'] = $row['notes'];
                $data['createtime'] = time();
                Db::name('contract_archive')->insertGetId($data);
            }
        }

        $this->success();
    }

}
