<?php

namespace app\admin\controller\unidrink;

use addons\unidrink\extend\Wechat;
use addons\unidrink\model\UserExtend;
use app\common\controller\Backend;

/**
 * 工作站
 *
 * @icon fa fa-circle-o
 */
class Workspace extends Backend
{

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'number_id';

    /**
     * @var \app\admin\model\unidrink\Order $model
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\unidrink\Order();
    }

    /**
     * 查看
     */
    public function index()
    {
        $shopIdName = (new \addons\unidrink\model\Shop())->getShopIdFromAdminId($this->auth->id);
        $shopId = $this->request->request('shop_id');
        if (!$shopId) {
            foreach ($shopIdName as $id => $name) {
                $shopId = $id;
                break;
            }
        }

        if ($this->request->isAjax()) {

            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);

            $total = $this->model
                ->where($where)
                ->where(['shop_id' => $shopId])
                ->where('have_paid > 0 && have_made = 0 && status = 1')
                ->count();

            $list = $this->model
                ->where($where)
                ->where(['shop_id' => $shopId])
                ->where('have_paid > 0 && have_made = 0 && status = 1')
                ->order('createtime ASC')
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            foreach ($list as &$value) {
                $value['id'] = (string)$value['id'];
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        $this->view->assign('shopList', build_select('row[shop_id]', $shopIdName, $shopId, ['class' => 'form-control selectpicker']));
        $this->view->assign('shop_id', $shopId);
        $this->view->assign('workspace_refresh', \addons\unidrink\model\Config::getByName('workspace_refresh')['value']);
        return $this->view->fetch();
    }

    /**
     * 详情
     */
    public function detail($ids)
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            $this->done($id);
        }
        $row = $this->model->with(['product', 'extend'])->where(['id' => $ids])->find();
        if (!$row) {
            $this->error('没有数据');
        }
        $address = $row['extend']['address_json'];
        if (!empty($address)) {
            $address = json_decode($address, true);
        }
        $this->view->assign('product', $row['product']);
        $this->view->assign('address', $address);
        $this->view->assign("order", $row->toArray());
        return $this->view->fetch();
    }

    /**
     * 取消订单
     */
    public function cancel($id) {
        $row = $this->model->with(['product'])->where(['id' => $id])->find();
        if (!$row) {
            $this->error('订单不存在');
        }
        // 把订单更新为取消状态
        $row->status = \addons\unidrink\model\Order::STATUS_CANCEL;
        $row->save();
        $this->success();
    }

    /**
     * 出单
     */
    public function done($id) {
        $row = $this->model->with(['product', 'shop'])->where(['id' => $id])->find();
        if (!$row) {
            $this->error('订单不存在');
        }

        // 自取 更新制作时间、出单时间
        $row->have_made = time();

        $row->save();

        // 发送微信订阅信息
        try {
            $app = Wechat::initEasyWechat('miniProgram');
            $tmp = Wechat::getMadeSubMsgFromType($row['type']);
            $user = UserExtend::get(['user_id' => $row['user_id']]);
            $data = Wechat::subscribeMsgData($tmp['template_data'], [
                '取餐码' => $row['number_id'],
                '温馨提示' => '祝你用餐愉快',
                '下单时间' => date('Y-m-d H:i:s', $row['createtime']),
                '取餐门店' => $row['shop']['name'],
                '订单编号' => $row['out_trade_no'],
                '配送时间' => date('Y-m-d H:i:s', $row['have_made']),
                '门店名称' => $row['shop']['name'],
            ]);
            $app->subscribe_message->send([
                'template_id' => $tmp['template_id'],
                'touser' => $user->openid, // 微信小程序openid
                'page' => 'pages/take-foods/take-foods',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            //@Db::execute("insert into fa_log (log) values ('{$e->getMessage()}')");
        }

        $this->success('已出单');
    }
}
