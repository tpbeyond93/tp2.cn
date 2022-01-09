<?php

namespace app\admin\controller\unidrink;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Validate;

/**
 * 优惠券管理
 *
 * @icon fa fa-circle-o
 */
class Coupon extends Backend
{

    /**
     * Coupon模型对象
     * @var \app\admin\model\unidrink\Coupon
     */
    protected $model = null;

    /**
     * 是否开启Validate验证
     */
    protected $modelValidate = true;

    /**
     * 是否开启模型场景验证
     */
    protected $modelSceneValidate = true;


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\unidrink\Coupon;
        $this->assign('typeList', $this->model->getTypeList());

        $shopData = \app\admin\model\unidrink\Shop::column('name', 'id');
        $shopData[0] = '全部';
        $this->view->assign('shopData', $shopData);
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
                ->with(['shop'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $shop = \app\admin\model\unidrink\Shop::column('name', 'id');
            $shop[0] = '全部';
            $list = collection($list)->toArray();

            foreach ($list as &$item) {
                $shop_ids = explode(',', $item['shop_id']);
                $item['shop_text'] = [];
                foreach ($shop_ids as $shop_id) {
                    if ($shop_id == 0) {
                        $item['shop_text'] = [];
                        $item['shop_text'][] = $shop[$shop_id];
                        break;
                    }
                    if (!empty($shop[$shop_id])) {
                        $item['shop_text'][] = $shop[$shop_id];
                    }
                }
                $item['shop_text'] = implode(',', $item['shop_text']);
            }

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }



    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
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
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    if ($params['least'] <= $params['value']) {
                        $this->error('消费多少可用 必须大于 优惠券金额');
                    }
                    $params['shop_id'] = implode(',', $params['shop_id']);
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    if (!empty($params['exchange_code'])) {
                        $exits = $this->model
                            ->where('id','<>' , $ids)
                            ->where('exchange_code', '=', $params['exchange_code'])->count();
                        if ($exits > 0) {
                            throw new ValidateException('兑换码不能重复');
                        }
                    }
                    if ($params['least'] <= $params['value']) {
                        $this->error('消费多少可用 必须大于 优惠券金额');
                    }
                    $params['shop_id'] = implode(',', $params['shop_id']);
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
