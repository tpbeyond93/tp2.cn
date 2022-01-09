<?php

namespace app\admin\controller\unidrink;

use app\common\controller\Backend;

/**
 * 用户账单
 *
 * @icon fa fa-circle-o
 */
class Bill extends Backend
{

    /**
     * Bill模型对象
     * @var \app\admin\model\unidrink\Bill
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\unidrink\Bill;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
    }


    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            foreach ($list as &$item) {
                $item['id'] = (string)$item['id'];
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}
