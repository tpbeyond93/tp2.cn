<?php

namespace app\admin\controller\unidrink;

use app\common\controller\Backend;
use fast\Tree;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 产品管理
 *
 * @icon fa fa-circle-o
 */
class Product extends Backend
{

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'title';

    /**
     * Multi方法可批量修改的字段
     */
    protected $multiFields = 'switch';

    /**
     * product模型对象
     * @var \app\admin\model\unidrink\Product
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\unidrink\Product;

        $shopData = \app\admin\model\unidrink\Shop::column('name', 'id');
        $shopData[0] = '全部';
        $this->view->assign('shopData', $shopData);

        $categoryList = \app\admin\model\unidrink\Category::where(['type' => 'product'])->order('weigh asc')->column('name', 'id');
        $this->view->assign('categoryList', $categoryList);
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

                    // 查看规格有没有添加
                    if (!empty($params['use_spec']) && $params['use_spec'] == 1) {
                        if (empty($params['specList']) || empty($params['specTableList']) || $params['specList'] == '""' || $params['specTableList'] == '""' || $params['specList'] == '[]' || $params['specTableList'] == '[]') {
                            throw new Exception('规格不能为空');
                        }
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
                ->count();

            $list = $this->model
                ->with([
                    'category'
                ])
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


    public function selectpage()
    {
        return parent::selectpage();
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

                    // 查看规格有没有添加
                    if (!empty($params['use_spec']) && $params['use_spec'] == 1) {
                        if (empty($params['specList']) || empty($params['specTableList']) || $params['specList'] == '""' || $params['specTableList'] == '""' || $params['specList'] == '[]' || $params['specTableList'] == '[]') {
                            throw new Exception('规格不能为空');
                        }
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

        $this->view->assign('categoryList', $this->build_category_select('row[category_id]', 'product', $row->category_id));

        return $this->view->fetch();
    }


    /**
     * 生成分类下拉列表框
     * @param string $name
     * @param string $type
     * @param mixed $selected
     * @param array $attr
     * @param array $header
     * @return string
     */
    protected function build_category_select($name, $type, $selected = null, $attr = [], $header = [])
    {
        $tree = Tree::instance();
        $tree->init(\app\admin\model\unidrink\Category::getCategoryArray($type), 'pid');
        $categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = $header ? $header : [];
        foreach ($categorylist as $k => $v) {
            $categorydata[$v['id']] = $v['name'];
        }
        $attr = array_merge(['id' => "c-{$name}", 'class' => 'form-control selectpicker'], $attr);
        return build_select($name, $categorydata, $selected, $attr);
    }

    /**
     * 选择附件
     */
    public function select()
    {
        if ($this->request->isAjax()) {
            return $this->index();
        }
        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function copy($ids = null)
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

                    // 查看规格有没有添加
                    if (!empty($params['use_spec']) && $params['use_spec'] == 1) {
                        if (empty($params['specList']) || empty($params['specTableList']) || $params['specList'] == '""' || $params['specTableList'] == '""' || $params['specList'] == '[]' || $params['specTableList'] == '[]') {
                            throw new Exception('规格不能为空');
                        }
                    }

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
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);

        $this->view->assign('categoryList', $this->build_category_select('row[category_id]', 'product', $row->category_id));

        return $this->view->fetch();
    }
}
