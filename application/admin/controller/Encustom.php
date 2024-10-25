<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Commonenter;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 企业个人关系管理
 *
 * @icon fa fa-circle-o
 */
class Encustom extends Backend
{

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
    public function index($ids=null)
    {

        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $this->assignconfig("enterId", $ids);
            return $this->view->fetch();
        }
        $enterId=$this->request->param('enterId');

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
        foreach($list as $k=>$v){
            if($v['custom']['attestation'] == 0){
                $list[$k]['custom']['attestation'] = '未认证';
            }else if($v['custom']['attestation'] == 1){
                $list[$k]['custom']['attestation'] = '已认证';
            }else if($v['custom']['attestation'] == 2){
                $list[$k]['custom']['attestation'] = '已认证';
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
            //验证成员是否已经存在企业
            $enterId=$this->request->param('enterId');
            if($enterId){
                $params['enterprise_id'] = $enterId;
                $encu = Db::name('enterprise_custom')
                    ->where('enterprise_id','=',$enterId)
                    ->where('custom_id','=',$params['custom_id'])
                    ->find();
                if($encu){
                    $this->error('请勿重复添加成员');
                }
            }
            $params['createtime'] = time();
            $encuId = Db::name('enterprise_custom')->insertGetId($params);
            $commonenter = new Commonenter();
            $commonenter->addmember($encuId);
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
            $commonenter = new Commonenter();
            foreach ($list as $item) {
                $commonenter->delmember($item['id']);

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
