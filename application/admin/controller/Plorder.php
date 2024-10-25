<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Common;
use app\common\controller\Commonorder;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 订单管理
 *
 * @icon fa fa-circle-o
 */
class Plorder extends Backend
{
    protected $noNeedLogin = ['edit'];

    /**
     * Plorder模型对象
     * @var \app\admin\model\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Order;

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
        $common = new Common();
        foreach($list as $k=>$v){
            $list[$k]['goods_id'] = $common->getgoods($v['goods_id'],'name')['name'];
            switch($v['state']){
                case 0:
                    $list[$k]['state'] = '待确认';
                    break;
                case 1:
                    $list[$k]['state'] = '待支付';
                    break;
                case 2:
                    $list[$k]['state'] = '已支付';
                    break;
                case 3:
                    $list[$k]['state'] = '已完成';
                    break;
                case 4:
                    $list[$k]['state'] = '已取消';
                    break;
            }
            if($v['type'] == 'enterprise'){
                $list[$k]['type'] = '企业';
                $list[$k]['type_id'] =$common->getenter($v['type_id'])['name'];
            }else if($v['type'] == 'custom'){
                $list[$k]['type'] = '个人';
                $list[$k]['type_id'] =$common->getcustom($v['type_id'])['name'];
            }

            if($v['payway'] == 0){
                $list[$k]['payway'] = '线上';
            }else{
                $list[$k]['payway'] = '线下';
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
        $enterId = $this->request->post('e-type_id');
        $customId = $this->request->post('c-type_id');

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
            if($params['type'] == 'enterprise'){
                $params['type_id'] = $enterId;
            }else{
                $params['type_id'] = $customId;
            }
            $commonorder = new Commonorder();
            $commonorder->addorder($params['type'],$params['type_id'],$params['goods_id'],$params['number']);
            $result = true;

//            $result = $this->model->allowField(true)->save($params);
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
        $row['paytime'] = time();
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
            //创建订单分佣
            $commonorder = new Commonorder();
            $commonorder->opeateorder(2,$ids);
            //支付状态更改
            $params['paystatus'] = 1;
            $params['state'] = 3;
            $params['payway'] = 1;
            $params['updatetime'] = time();

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


}
