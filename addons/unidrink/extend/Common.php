<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/6/10
 * Time: 9:10 AM
 */


namespace addons\unidrink\extend;

use addons\unidrink\model\Shop;

/**
 * 公共类
 * Class Common
 * @package addons\unidrink\extend
 */
class Common
{
    /**
     * 检测经纬度距离
     */
    public static function getDistanceFromLocation($lat = 0, $lng = 0, $lat2 = 0, $lng2 = 0)
    {
        $pi = pi();
        $lat = $lat * $pi / 180.0;
        $lat2 = $lat2 * $pi / 180.0;
        $la3 = $lat - $lat2;
        $lb3 = $lng * $pi / 180.0 - $lng2 * $pi / 180.0;
        $distance = 2 * asin(sqrt(pow(sin($la3 / 2), 2) + cos($lat) * cos($lat2) * pow(sin($lb3 / 2), 2)));
        $distance = $distance * 6378.137;
        $distance = round($distance * 10000) / 10;

        return number_format($distance/1000, 3);
    }

    /**
     * 给出店铺id字符串，返回全部店铺信息
     * @param string $ids
     * @return array [['id' => '店铺名称'],...]
     * @throws \think\exception\DbException
     */
    public static function getShopByIds(string $ids) : array
    {
        $shopId = rtrim($ids, ',');
        $shopId = explode(',', $shopId);
        $shopId = array_unique($shopId);
        $shops = (new Shop)->where('id','in',$shopId)->column('name', 'id');
        return $shops;
    }

    /**
     * 根据$ids 获取 shops里面的店铺名name
     * @param array $shops
     * @param string $ids
     * @return string
     * @throws \think\exception\DbException
     */
    public static function getShopNameByIds(array $shops,string $ids) : string
    {
        $shopId = rtrim($ids, ',');
        $shopId = explode(',', $shopId);
        $shopName = [];
        foreach ($shopId as $key => $value) {
            if ($value == 0) {
                return '所有门店';
            } else {
                if (isset($shops[$value])) {
                    $shopName[] = $shops[$value];
                }
            }
        }
        return implode('，', $shopName);
    }
}
