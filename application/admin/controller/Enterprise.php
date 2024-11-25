<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Common;
use app\common\controller\Commonattestation;
use app\common\controller\Commonenter;
use app\common\controller\Commonuser;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 企业用户管理
 *
 * @icon fa fa-circle-o
 */
class Enterprise extends Backend
{
    protected $noNeedLogin = ['entrance','edit'];

    /**
     * Enterprise模型对象
     * @var \app\admin\model\Enterprise
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Enterprise;

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
            if($v['finishedTime']){
                $list[$k]['finishedTime'] = $v['finishedTime']/1000;
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
            $enter = Db::name('enterprise')->where('proveNo','=',$params['proveNo'])->find();
            if($enter){
                $this->error('企业已存在，请勿重复添加');
            }
//            $city = explode('/',$params['city']);
//            $params['province'] = $city[0];
//            $params['city'] = $city[1];
//            $params['area'] = $city[2];
            $commonenter = new Commonenter();
            $enterId = $commonenter->operateenter($params);

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
            $commonenter = new Commonenter();
            $commonenter->operateenter($params,$row['id']);
            $result = true;
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
     * ps:企业认证
     */
    public function attestation($ids=null){
        $row = $this->model->get($ids);

        $commonattestation = new Commonattestation();
        //$redirectUrl ='https://'.$_SERVER['HTTP_HOST'].'/h5/#/pages/login/login';
        $redirectUrl = '';//需要小程序链接
        $res = $commonattestation->enterprise($ids,$redirectUrl);
        if($res['code'] == 300){
            $this->error($res['msg']);
        }
        $common = new Common();
        $url = $common->addqrcode($res['identifyUrl']);
        $this->assign('url',$url);
        $this->assign('row',$row);

        return $this->view->fetch();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月24月 17:22:57
     * ps:
     */
    public function account(){
       $enterId = $this->getenter();
        $enter = Db::name('enterprise')->where('id','=',$enterId)->find();
        $account = Db::name('account')->where('type','=','enterprise')->where('type_id','=',$enterId)->find();
        $this->assign('enter',$enter);
        $this->assign('account',$account);
        return $this->view->fetch();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月25月 9:40:46
     * ps:企业详情
     */
    public function details(){
        $enterId = $this->getenter();
        $enter = Db::name('enterprise')->where('id','=',$enterId)->find();
        $account = Db::name('account')->where('type','=','enterprise')->where('type_id','=',$enterId)->find();
        $account['contractnum'] = $account['contract'] + $account['usecontract'];
        $account['templatenum'] = $account['template'] + $account['usetemplate'];
        $this->assign('enter',$enter);
        $this->assign('account',$account);
        return $this->view->fetch();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月23月 11:36:33
     * ps:企业入口
     */
    public function entrance($ids = null){
        $common = new Common();
        $hturl = 'https://'.$_SERVER['HTTP_HOST'].'/fradmin.php/index/login?platformId='.$ids;

        $url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/pages/login/login?platformId='.$ids;
        $qrcode = $common->addqrcode($url);
        $this->assign('qrcode',$qrcode);
        $this->assign('url',$url);
        $this->assign('hturl',$hturl);

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

                $encu = Db::name('enterprise_custom')->where('enterprise_id','=',$item['id'])->delete();
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
