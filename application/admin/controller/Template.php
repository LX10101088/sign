<?php

namespace app\admin\controller;

use app\api\controller\Csms;
use app\common\controller\Backend;
use app\common\controller\Commoncontract;
use app\common\controller\Commoninfo;
use app\common\controller\Commontemplate;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
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
    protected $noNeedLogin = ['contenturl','del'];

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
            if(!$params['file']){
                $this->error('请上传模版文件');
            }
            if(!$params['name']){
                $this->error('请输入模版名称');
            }
            $extension = 'docx';
            $lastDotPosition = strrpos($params['file'], '.');
            // 如果找到了 '.'，则提取 '.' 后面的部分
            if ($lastDotPosition !== false) {
                $extension = substr($params['file'], $lastDotPosition + 1);
            }
            $params['filename'] = $params['name'].'.'.$extension;
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

        $annexlist = Db::name('template_img')->where('template_id','=',$ids)->order('id ASC')->select();
        $annex  = '';
        foreach($annexlist as $k=>$v){
            $annex .= $v['img'].',';
        }
        $this->assign('userId',$this->auth->user_id);
        $this->assign('annex',$annex);

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
        $preview = $this->request->post('preview');

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

            if($row['type_id'] !=0){
                //查询是否扣费，如果没有进行扣费
                if($params['state']== 2){
                    if($row['charging'] == 0){
                        $account = Db::name('account')->where('type','=',$row['type'])->where('type_id','=',$row['type_id'])->find();
                        if($account['template'] < 1){
                            $this->error('模板发布者，账户余额不足');
                        }
                        //扣除账户模版份数
                        $acedit['template'] = $account['template'] -1;
                        $acedit['usetemplate'] = $account['usetemplate'] +1;
                        $acedit['updatetime'] = time();
                        Db::name('account')->where('type','=',$row['type'])->where('type_id','=',$row['type_id'])->update($acedit);
                        $params['charging'] = 1;
                    }

                }
                if($this->auth->usertype != 'custom'){
                    if($row['state'] != 2){
                        //发送短信
                        $sms = new Csms();
                        $sms->templateopen($row['id']);
                    }
                }
            }

//            Db::name('template_img')->where('template_id','=',$ids)->delete();
//
//            $array = explode(",", $preview);
//            foreach($array as $k=>$v){
//                if($v){
//                    $data['createtime'] = time();
//                    $data['img'] = $v;
//                    $data['template_id'] =$ids;
//                    Db::name('template_img')->insertGetId($data);
//                }
//            }
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
