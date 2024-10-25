<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Templatecontent extends Backend
{

    /**
     * Templatecontent模型对象
     * @var \app\admin\model\Templatecontent
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Templatecontent;

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
    public function index($ids=null)
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $this->assignconfig("templateId", $ids);

            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $templateId=$this->request->param('templateId');

        $list = $this->model
            ->where($where)
            ->where('template_id','=',$templateId)
            ->order($sort, $order)
            ->paginate($limit);

        foreach($list as $k=>$v){
            switch($v['type']){
                case 1:
                    $list[$k]['type'] = '文本';
                    break;
                case 2:
                    $list[$k]['type'] = '日期';
                    break;
                case 3:
                    $list[$k]['type'] = '图片';
                    break;

                case 4:
                    $list[$k]['type'] = '备注签署';
                    break;
                case 5:
                    $list[$k]['type'] = '个人签署区+日期';
                    break;
                case 6:
                    $list[$k]['type'] = '企业签署区+日期';
                    break;
                case 7:
                    $list[$k]['type'] = '勾选框';
                    break;
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
        $templateId=$this->request->param('templateId');

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
            $params['template_id'] = $templateId;
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
}
