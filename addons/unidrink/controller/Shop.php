<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/5/31
 * Time: 11:43 AM
 */


namespace addons\unidrink\controller;

use addons\unidrink\extend\Common;

/**
 * 店铺接口
 * Class Shop
 * @package addons\unidrink\controller
 */
class Shop extends Base
{
    protected $noNeedLogin = ['nearby', 'getList'];

    /**
     * @ApiTitle    (附近最近的门店)
     * @ApiSummary  (如果传了shop_id则返回这个店铺的信息，如果没传则根据经纬度判断返回最近的店铺信息)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiParams   (name="lat", type="float", required=true, description="经度")
     * @ApiParams   (name="lng", type="float", required=true, description="纬度")
     * @ApiParams   (name="shop_id", type="integer", required=false, description="店铺id")
     * @ApiReturn   ({"code":1,"msg":"","data":{}})
     *
     * @ApiReturnParams  (name="id", type="integer", description="店铺id")
     * @ApiReturnParams  (name="name", type="string", description="店铺名称")
     * @ApiReturnParams  (name="mobile", type="string", description="店铺电话")
     * @ApiReturnParams  (name="notice", type="string", description="店铺公告")
     * @ApiReturnParams  (name="min_price", type="string", description="最低起送价格")
     * @ApiReturnParams  (name="delivery_price", type="string", description="配送费/运费")
     * @ApiReturnParams  (name="lat", type="float", description="经度")
     * @ApiReturnParams  (name="lng", type="float", description="纬度")
     * @ApiReturnParams  (name="distance", type="float", description="配送距离，0表示不配送外卖")
     * @ApiReturnParams  (name="address", type="string", description="店铺地址详情")
     * @ApiReturnParams  (name="address_map", type="string", description="地图选择的地址")
     * @ApiReturnParams  (name="image", type="string", description="店铺logo")
     * @ApiReturnParams  (name="status", type="integer", description="是否营业中:0=否,1=是")
     * @ApiReturnParams  (name="status_text", type="integer", description="营业状态")
     * @ApiReturnParams  (name="far", type="float", description="距离")
     * @ApiReturnParams  (name="far_text", type="float", description="距离/km")
     * @ApiReturnParams  (name="bussines_time", type="string", description="营业时间")
     *
     */
    public function nearby()
    {
        $lat = $this->request->post('lat');
        $lng = $this->request->post('lng');
        $shopId = $this->request->post('shop_id', 0);

        if (!$lat || !$lng) {
            $this->error('参数错误');
        }

        $shopModel = new \addons\unidrink\model\Shop();
        $shop = $shopModel->getList($lat, $lng, 1, 1, $shopId);

        if ($shop && is_array($shop)) {
            $shop = $shop[0];
            $shop->append(['status_text'])->toArray();
            $shop['far_text'] = number_format($shop['far'], 3) . 'km';
        }
        $this->success('', $shop);
    }

    /**
     * @ApiTitle    (获取所有店铺)
     * @ApiSummary  (获取所有店铺)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiParams   (name="lat", type="float", required=true, description="经度")
     * @ApiParams   (name="lng", type="float", required=true, description="纬度")
     * @ApiParams   (name="kw", type="string", required=false, description="模糊搜索")
     * @ApiParams   (name="page", type="integer", required=false, description="页面")
     * @ApiParams   (name="pagesize", type="integer", required=false, description="页数")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="integer", description="店铺id")
     * @ApiReturnParams  (name="name", type="string", description="店铺名称")
     * @ApiReturnParams  (name="mobile", type="string", description="店铺电话")
     * @ApiReturnParams  (name="notice", type="string", description="店铺公告")
     * @ApiReturnParams  (name="min_price", type="string", description="最低起送价格")
     * @ApiReturnParams  (name="delivery_price", type="string", description="配送费/运费")
     * @ApiReturnParams  (name="lat", type="float", description="经度")
     * @ApiReturnParams  (name="lng", type="float", description="纬度")
     * @ApiReturnParams  (name="distance", type="float", description="配送距离，0表示不配送外卖")
     * @ApiReturnParams  (name="address", type="string", description="店铺地址详情")
     * @ApiReturnParams  (name="address_map", type="string", description="地图选择的地址")
     * @ApiReturnParams  (name="image", type="string", description="店铺logo")
     * @ApiReturnParams  (name="status", type="integer", description="是否营业中:0=否,1=是")
     * @ApiReturnParams  (name="status_text", type="integer", description="营业状态")
     * @ApiReturnParams  (name="far", type="float", description="距离")
     * @ApiReturnParams  (name="far_text", type="float", description="距离/km")
     * @ApiReturnParams  (name="bussines_time", type="string", description="营业时间")
     *
     */
    public function getList()
    {
        $keyword = $this->request->post('kw', '');
        $lat = $this->request->post('lat');
        $lng = $this->request->post('lng');
        $page = $this->request->post('page', 1);
        $pagesize = $this->request->post('pagesize', 10);

        if (!$lat || !$lng) {
            $this->error('参数错误');
        }

        $shopModel = new \addons\unidrink\model\Shop();
        $list = $shopModel->getList($lat, $lng, $page, $pagesize, 0, $keyword);

        if ($list) {
            $list = collection($list)->append(['status_text'])->toArray();
            foreach ($list as &$shop) {
                $shop['far_text'] = number_format($shop['far'], 3) . 'km';
            }
        }

        $this->success('', $list);
    }

    /**
     * @ApiTitle    (获取两个经纬度之间的距离)
     * @ApiSummary  (获取两个经纬度之间的距离)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiHeaders  (name=token, type=string, required=false, description="登录token")
     * @ApiParams   (name="lat", type="float", required=true, description="经度")
     * @ApiParams   (name="lng", type="float", required=true, description="纬度")
     * @ApiParams   (name="lat2", type="float", required=true, description="经度")
     * @ApiParams   (name="lng2", type="float", required=true, description="纬度")
     * @ApiReturn   ({"code":1,"msg":"","data": "亮点距离/km"})
     *
     */
    public function getDistanceFromLocation()
    {
        $lat = $this->request->post('lat', 0);
        $lng = $this->request->post('lng', 0);
        $lat2 = $this->request->post('lat2', 0);
        $lng2 = $this->request->post('lng2', 0);

        $distance = Common::getDistanceFromLocation($lat, $lng, $lat2, $lng2);

        $this->success('', $distance);
    }
}
