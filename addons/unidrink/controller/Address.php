<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2019/11/5
 * Time: 10:33 下午
 */


namespace addons\unidrink\controller;

use \addons\unidrink\model\Address as AddressModel;
use think\Exception;
use think\Loader;
use think\Validate;

/**
 * 收货地址接口
 * Class Address
 * @package addons\unidrink\controller
 */
class Address extends Base
{

    /**
     * @ApiTitle    (全部收货地址)
     * @ApiSummary  (用户收货地址列表)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=token, type=string, required=true, description="用户登录的Token", sample="a2e3cc70-d2d1-41e6-9c14-f1d774ee5e1e")
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams   (name="page", type=integer, required=false, description="页面")
     * @ApiParams   (name="pagesize", type=integer, required=false, description="页数")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     * @ApiReturnParams  (name="id", type="integer", description="地址id")
     * @ApiReturnParams  (name="user_id", type="integer", description="用户id")
     * @ApiReturnParams  (name="name", type="string", description="收货人名称")
     * @ApiReturnParams  (name="mobile", type="string", description="收货人电话")
     * @ApiReturnParams  (name="address", type="string", description="收货详细地址")
     * @ApiReturnParams  (name="lng", type="string", description="经度")
     * @ApiReturnParams  (name="lat", type="string", description="纬度")
     * @ApiReturnParams  (name="door_number", type="string", description="门牌号")
     * @ApiReturnParams  (name="sex", type="integer", description="性别")
     */
    public function all()
    {
        $page = $this->request->post('page', 1);
        $pagesize = $this->request->post('pagesize', 15);

        $data = (new AddressModel())
            ->where('user_id', $this->auth->id)
            ->order(['id' => 'desc'])
            ->limit(($page - 1) * $pagesize, $pagesize)
            ->select();

        if ($data) {
            $data = collection($data)->toArray();
        }

        $this->success('', $data);
    }


    /**
     * @ApiTitle    (添加收货地址)
     * @ApiSummary  (添加收货地址)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=token, type=string, required=true, description="用户登录的Token", sample="a2e3cc70-d2d1-41e6-9c14-f1d774ee5e1e")
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams  (name="name", type="string", description="收货人名称")
     * @ApiParams  (name="mobile", type="string", description="收货人电话")
     * @ApiParams  (name="address", type="string", description="收货详细地址")
     * @ApiParams  (name="lng", type="string", description="经度")
     * @ApiParams  (name="lat", type="string", description="纬度")
     * @ApiParams  (name="door_number", type="string", description="门牌号")
     * @ApiParams  (name="sex", type="integer", description="性别")
     *
     * @ApiReturn   ({"code":1,"msg":"","data":true})
     */
    public function add()
    {
        $data = $this->request->post();
        try {
            $validate = Loader::validate('\\addons\\unidrink\\validate\\Address');
            if (!$validate->check($data, [], 'add')) {
                throw new Exception($validate->getError());
            }

            $data['user_id'] = $this->auth->id;

            $addressModel = new AddressModel();
            if ($addressModel->where(['user_id' => $this->auth->id])->count() > 49) {
                throw new Exception('不能添加超过50个地址');
            }

            if (!$addressModel->allowField(true)->save($data)) {
                throw new Exception($addressModel->getError());
            }
        } catch (Exception $e) {
            $this->error($e->getMessage(), false);
        }
        $this->success('添加成功', true);
    }


    /**
     * @ApiTitle    (修改收货地址)
     * @ApiSummary  (修改收货地址)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=token, type=string, required=true, description="用户登录的Token", sample="a2e3cc70-d2d1-41e6-9c14-f1d774ee5e1e")
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams  (name="id", type="string", description="收货地址id")
     * @ApiParams  (name="name", type="string", description="收货人名称")
     * @ApiParams  (name="mobile", type="string", description="收货人电话")
     * @ApiParams  (name="address", type="string", description="收货详细地址")
     * @ApiParams  (name="lng", type="string", description="经度")
     * @ApiParams  (name="lat", type="string", description="纬度")
     * @ApiParams  (name="door_number", type="string", description="门牌号")
     * @ApiParams  (name="sex", type="integer", description="性别")
     *
     * @ApiReturn   ({"code":1,"msg":"","data":true})
     */
    public function edit()
    {
        $data = $this->request->post();
        try {
            new Validate();
            $validate = Loader::validate('\\addons\\unidrink\\validate\\Address');
            if (!$validate->check($data, [], 'edit')) {
                throw new Exception($validate->getError());
            }

            $addressModel = new AddressModel();
            $data['user_id'] = $this->auth->id;

            $data['updatetime'] = time();
            if (!$addressModel->allowField(true)->save($data, ['id' => $data['id'], 'user_id' => $data['user_id']])) {
                throw new Exception($addressModel->getError());
            }
        } catch (Exception $e) {
            $this->error($e->getMessage(), false);
        }
        $this->success('修改成功', true);
    }


    /**
     * @ApiTitle    (删除收货地址)
     * @ApiSummary  (删除收货地址)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=token, type=string, required=true, description="用户登录的Token", sample="a2e3cc70-d2d1-41e6-9c14-f1d774ee5e1e")
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams  (name="id", type="string", description="收货地址id")
     *
     * @ApiReturn   ({"code":1,"msg":"","data":true})
     */
    public function delete()
    {
        $address_id = $this->request->post('id', 0);

        $data = (new AddressModel())
            ->where([
                'id' => $address_id,
                'user_id' => $this->auth->id
            ])
            ->delete();

        if ($data) {
            $this->success('删除成功', 1);
        } else {
            $this->success('没有数据', 0);
        }
    }

}
