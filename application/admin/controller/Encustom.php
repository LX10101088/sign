<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Commonenter;
use app\common\controller\Commonsignature;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;
use think\Url;

/**
 * 企业个人关系管理
 *
 * @icon fa fa-circle-o
 */
class Encustom extends Backend
{
    protected $noNeedLogin = ['sealaccredit','ajax_sealaccredit'];

    /**
     * Encustom模型对象
     * @var \app\admin\model\Encustom
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Encustom;

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
            $purview = $this->getuserauth();

            $this->assign("purview", $purview);
            return $this->view->fetch();
        }
//        $enterId=$this->request->param('enterId');

//        if($enterId == null || !$enterId){
        $enterId = $this->getenter();

//        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->with(['custom'])
            ->where($where)
            ->where('enterprise_id','=',$enterId)
            ->order($sort, $order)
            ->paginate($limit);
        $purview = $this->getuserauth();

        foreach($list as $k=>$v){
            if($v['custom']['attestation'] == 0){
                $list[$k]['custom']['attestation'] = '未认证';
            }else if($v['custom']['attestation'] == 1){
                $list[$k]['custom']['attestation'] = '已认证';
            }else if($v['custom']['attestation'] == 2){
                $list[$k]['custom']['attestation'] = '已认证';
            }
            $list[$k]['dqauth'] = $purview;
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
            //验证成员是否已经存在企业

            $enterId = $this->getenter();

            $commonenter = new Commonenter();
            $res = $commonenter->addplatemember($enterId,$params['name'],$params['phone'],$params['identityNo']);
            if($res == 300){
                $this->error('手机号已注册，输入身份信息与认证信息不同');
            }else if($res == 301){
                $this->error('成员已存在，请勿重复添加');
            }
            //$result = $this->model->allowField(true)->save($params);
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
        $array = explode(",", $ids);
        $commonenter = new Commonenter();
        foreach($array as $k=>$v){
            if($v){
                $encu = Db::name('enterprise_custom')
                    ->where('id','=',$ids)
                    ->find();
                if($encu['purview'] == 1 || $encu['purview'] == 0){
                    $this->error('请勿删除超级管理员');
                }

                $commonenter->delmember($v);
            }
        }
        $this->success('删除成功');
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月13月 15:17:32
     * ps:法大大企业人员添加认证
     */
    public function fadada($ids = null){
        $encu = Db::name('enterprise_custom as e')
            ->join('custom','e.custom_id=custom.id')
            ->where('e.id','=',$ids)
            ->find();

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月14月 10:06:08
     * ps:印章授权
     */
    public function sealaccredit($ids = null){
        $encu = Db::name('enterprise_custom')
            ->where('id','=',$ids)
            ->find();
        if (false === $this->request->isPost()) {
            $date = date('Y-m-d H:i:s',time());
            $timestamp = strtotime($date . ' +1 day'); // 将日期字符串转换为时间戳并增加一天

            $this->assign('time',date('Y-m-d H:i:s',$timestamp));
            $this->assign('ids',$ids);

            $this->assign('enterId',$encu['enterprise_id']);
            return $this->view->fetch();
        }

    }

    public function ajax_sealaccredit(){
        $ids = $_POST['ids'];
        $signatureId = $_POST['signatureId'];

        $time = $_POST['endtime'];

        $encu = Db::name('enterprise_custom')
            ->where('id','=',$ids)
            ->find();

        $encusign = Db::name('enterprise_custom_signature')
            ->where('encu_id','=',$ids)
            ->where('signature_id','=',$signatureId)
            ->where('endtime','>',time())
            ->find();

        if($encusign){
            if($encusign['endtime']){
                $res['code'] = 300;
                $res['msg'] = '印章已授权，请勿重复授权';
                return $res;
            }
        }
        $starttime = time().'000';
        $endtime = strtotime($time).'000';
        $userId = $this->auth->user_id;
        $commonsignature = new Commonsignature();
        $res = $commonsignature->sealauthorize($encu['enterprise_id'],$userId,$ids,$signatureId,$starttime,$endtime);
        return $res;
    }



}
