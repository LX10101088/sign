<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Commoncontract;
use app\common\controller\Commoninfo;
use app\common\controller\Commontemplate;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 合同模版管理
 *
 * @icon fa fa-circle-o
 */
class Template extends Backend
{
    protected $noNeedLogin = ['contenturl'];

    /**
     * Template模型对象
     * @var \app\admin\model\Template
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Template;

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
        $common = new Commoninfo();
        foreach($list as $k=>$v){
            if($this->auth->usertype == 'custom'){
                $list[$k]['typeqx'] = 1;

            }else{
                $list[$k]['typeqx'] = 0;

            }
            if($v['type'] == 'enterprise'){
                $list[$k]['type'] = '企业';
                $list[$k]['type_id'] =$common->getenter($v['type_id'])['name'];
            }else if($v['type'] == 'custom'){
                $list[$k]['type'] = '个人';
                $list[$k]['type_id'] =$common->getcustom($v['type_id'])['name'];
            }

            if($v['state'] == 0){
                $list[$k]['state'] ='待生成';
            }else if($v['state'] == 1){
                $list[$k]['state'] ='生成中';
            }else if($v['state'] == 2){
                $list[$k]['state'] ='启用中';
            }else if($v['state'] == 3){
                $list[$k]['state'] ='已停用';
            }
            if($v['classify_id']){
                $list[$k]['classify_id'] = $common->gettemclassify($v['classify_id'])['name'];
            }else{
                $v['classify_id'] = '-';
            }
            if(!$v['type_id']){
                $v['type_id'] = '平台';
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
        if($this->auth->usertype == 'custom'){
            $enterId = $this->getenter();
        }else{
            $enterId = 0;
        }

        if (false === $this->request->isPost()) {
            if($enterId){
                //查询账户余额
                $account = Db::name('account')->where('type','=','enterprise')->where('type_id','=',$enterId)->find();
                if($account['template']<1){
                    $this->error('账户余额不足，无法创建模版');
                }
            }
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

            $params['type'] = '';
            $params['type_id'] = '';
            if($enterId){
                $params['type'] = 'enterprise';
                $params['type_id'] = $enterId;
            }
            $commontemplate = new Commontemplate();
            $commontemplate->operatetemplate($params,$params['type'],$params['type_id']);

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
        $this->assign('userId',$this->auth->user_id);

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
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
     * time:2024年9月06月 9:30:11
     * ps:下载
     */
    public function download($ids= null){
        $template = Db::name('template')->where('id','=',$ids)->find();
        $url = $template['file'];
        if($url){
            // 使用cURL获取文件内容
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $fileContent = curl_exec($ch);
            curl_close($ch);

            if ($fileContent !== false) {
                // 设置文件类型（这里以PDF为例）
                header('Content-Type: application/pdf');

                // 设置下载头
                header('Content-Disposition: attachment; filename="'.$template['name'].'".pdf"');


                // 发送文件内容
                echo $fileContent;

                // 终止脚本执行
                $this->success('下载成功');
            } else {
                // 处理错误情况
                $this->error('下载失败');
            }
        }else{
            $this->error('下载失败');

        }

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月24月 15:31:46
     * ps:模版内容
     */
    public function contenturl($ids=null){
        $commontemplate = new Commontemplate();
        $res = $commontemplate->gettemplateurl($ids);
        if($res['code'] == 200){
            $this->assign('url',$res['url']);
            return $this->view->fetch();
        }else{
            $this->error($res['msg']);
        }
    }

}
