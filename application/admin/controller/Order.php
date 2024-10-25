<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Common;
use app\common\controller\Commonorder;
use think\Db;
use think\exception\DbException;
use think\response\Json;

/**
 * 订单管理
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{
    protected $noNeedLogin = ['addorder','qrorder','payment'];
    /**
     * Order模型对象
     * @var \app\admin\model\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Order;

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

        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }




    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 11:05:10
     * ps:下单
     */
    public function addorder(){
        $goodsId = $_GET['goodsId'];
        $enterId = $this->getenter();
        $goods = Db::name('goods')->where('id','=',$goodsId)->find();
        $this->assign('goods',$goods);
        $this->assign('enterId',$enterId);
        return $this->view->fetch();
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 13:25:49
     * ps:确认订单
     */
    public function qrorder(){
        $number = $_POST['number'];
        $goodsId = $_POST['goodsId'];
        $enterId = $_POST['enterId'];
        $commonorder = new Commonorder();
        $orderId = $commonorder->addorder('enterprise',$enterId,$goodsId,$number);
        $data['orderId'] = $orderId;
        $data['code'] = 200;
        return $data;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 13:40:54
     * ps:支付订单
     */
    public function payment($orderId){
        if(!$orderId){
            $orderId = $_GET['orderId'];

        }
        $order = Db::name('order')->where('id','=',$orderId)->find();
        $goods = Db::name('goods')->where('id','=',$order['goods_id'])->find();
        $url = '';
        if($order['wxurl']){
            $url = $order['wxurl'];
        }else{
            $commonorder = new Commonorder();
            $rest = $commonorder->nativeorder($orderId);
            if($rest['code'] == 300){
                $this->error('支付发起失败');
            }
            $url = $rest['url'];
        }
        $this->assign('goods',$goods);

        $this->assign('order',$order);
        $this->assign('url',$url);

        return $this->view->fetch();
    }
}
