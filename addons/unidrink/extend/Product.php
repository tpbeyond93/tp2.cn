<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/3/18
 * Time: 5:26 PM
 */


namespace addons\unidrink\extend;

/**
 * 商品相关逻辑
 * Class Product
 * @package addons\unidrink\extend
 */
class Product
{
    /**
     * 获取商品的基础信息
     * @param array $product 商品信息数组
     * @param string $spec 规格值，用,号隔开
     * @param string $key 要获取的字段
     * @return array
     */
    public function getBaseData(array $product, string $spec = '', string $key = '')
    {
        if (!$product) {
            return [];
        }
        $data = [];
        if ($spec && $product['use_spec'] == \addons\unidrink\model\Product::SPEC_ON && !empty($product['specTableList'])) {
            $specValueArr = json_decode($product['specTableList'], true);
            foreach ($specValueArr as $k => $specItem) {
                if (implode(',', $specItem['value']) == $spec) {
                    if ($key) {
                        $data = $specItem[$key];
                    } else {
                        $data = $specItem;
                        $data['key'] = $k;
                    }
                }
            }
        }
        if (empty($data)) {
            if ($key) {
                $data = $product[$key];
            } else {
                $data['market_price'] = $product['market_price'];
                $data['sales_price'] = $product['sales_price'];
                $data['stock'] = $product['stock'];
                $data['sales'] = $product['sales'];
                $data['image'] = $product['image'];
            }
        }
        if (is_array($data)){
            $data['image'] = $data['image'] ? $data['image'] : $product['image'];
        }
        return $data;
    }
}
