<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Common;
use app\common\controller\Commonattestation;
use app\common\controller\Commonuser;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 个人用户管理
 *
 * @icon fa fa-circle-o
 */
class Custom extends Backend
{

    /**
     * Custom模型对象
     * @var \app\admin\model\Custom
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Custom;

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
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $commoninfo = new \app\common\controller\Commoninfo();
        foreach($list as $k=>$v){
            if($v['service_id']){
                $list[$k]['service_id'] = $commoninfo->getservice($v['service_id'],'name')['name'];
            }else{
                $list[$k]['service_id'] = '-';
            }
            if($v['attestation'] == 0){
                $list[$k]['attestation'] = '未认证';
            }else if($v['attestation'] == 1){
                $list[$k]['attestation'] = '已认证';
            }else if($v['attestation'] == 2){
                $list[$k]['attestation'] = '已认证';
            }
            if(!$v['name']){
                $v['name'] = '普通用户';
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
//            $city = explode('/',$params['city']);
//            $params['province'] = $city[0];
//            $params['city'] = $city[1];
//            $params['area'] = $city[2];
//            $common = new \app\common\controller\Common();
//            $params['identifier'] = $common->userNo($city[1],2);
            $commonuser = new Commonuser();
            $commonuser->operatecustom($params);
//            $result = $this->model->allowField(true)->save($params);
            $result = true;
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
            $row['city'] = $row['province'].'/'.$row['city'].'/'.$row['area'];

            $this->view->assign('row', $row);
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
//            $city = explode('/',$params['city']);
//            $params['province'] = $city[0];
//            $params['city'] = $city[1];
//            $params['area'] = $city[2];
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
     * time:2024年9月03月 10:06:24
     * ps:个人认证
     */
    public function attestation($ids=null){
        $row = $this->model->get($ids);
        if(!$row['name'] && !$row['phone'] && !$row['identityNo']){
            $this->error('请填全信息后进行认证');
        }
        $redirectUrl ='https://'.$_SERVER['HTTP_HOST'].'/h5/#/pages/login/login';

        $commonattestation = new Commonattestation();
        $res = $commonattestation->custom($ids,$redirectUrl);
        if($res['code'] == 300){
            $this->error($res['msg']);
        }else if($res['code']==201){
            $this->success($res['msg']);
        }
        $common = new Common();
        $url = $common->addqrcode($res['identifyUrl']);
        $this->assign('url',$url);
        $this->assign('row',$row);

        return $this->view->fetch();
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
            foreach ($list as $item) {
                $count += $item->delete();
                //删除admin内容
                Db::name('admin')->where('user_id','=',$item['id'])->delete();
                //删除企业个人关系
                Db::name('enterprise_custom')->where('custom_id','=',$item['id'])->delete();
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
