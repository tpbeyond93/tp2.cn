<?php

namespace addons\unidrink\controller;


use addons\unidrink\model\Ads;
use addons\unidrink\model\Category;
use addons\unidrink\model\Product;

/**
 * 菜单接口
 * Class Menu
 * @package addons\unidrink\controller
 */
class Menu extends Base
{

    protected $noNeedLogin = ['goods', 'ads'];
    protected $noNeedRight = ['*'];


    /**
     * @ApiTitle    (全部菜品)
     * @ApiSummary  (获得属于某个门店的所有商品分类和商品)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="shop_id", type=integer, required=true, description="商家id")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="integer", description="分类id")
     * @ApiReturnParams  (name="name", type="string", description="分类名称")
     * @ApiReturnParams  (name="icon", type="string", description="分类图片")
     * @ApiReturnParams  (name="sort", type="integer", description="分类排序")
     * @ApiReturnParams  (name="goods_list", type="array", description="此分类下的商品")
     *
     * @ApiReturnParams  (name="goods_list.id", type="integer", description="商品id")
     * @ApiReturnParams  (name="goods_list.category_id", type="integer", description="分类id")
     * @ApiReturnParams  (name="goods_list.image", type="string", description="商品图片")
     * @ApiReturnParams  (name="goods_list.sales_price", type="string", description="商品销售价")
     * @ApiReturnParams  (name="goods_list.name", type="string", description="商品名称")
     * @ApiReturnParams  (name="goods_list.desc", type="string", description="商品详情")
     * @ApiReturnParams  (name="goods_list.use_spec", type="integer", description="是否使用规格")
     * @ApiReturnParams  (name="goods_list.sales", type="integer", description="销量")
     * @ApiReturnParams  (name="goods_list.specList", type="integer", description="规格属性")
     * @ApiReturnParams  (name="goods_list.specTableList", type="integer", description="规格值")
     *
     */
    public function goods() {
        $shop_id = $this->request->post('shop_id', 0);
        $categoryModel = new Category();
        $all = $categoryModel
            ->with(['goodsList' => function($query) use ($shop_id) {
                $query
                    ->where(['switch' => Product::SWITCH_ON])
                    ->where('shop_id = '.Product::ALL_SHOP.' OR FIND_IN_SET(:shop_id, shop_id)', ['shop_id' => $shop_id])
                    ->field('id,category_id,image,sales_price,title as name,`desc`,use_spec,real_sales,sales,specList,specTableList,stock')
                    ->order('weigh DESC');
            }])
            ->where('type','product')
            ->where('status','normal')
            ->where('shop_id = '.Category::ALL_SHOP.' OR FIND_IN_SET(:shop_id, shop_id)', ['shop_id' => $shop_id])
            ->field('id,name,image as icon,weigh as sort')
            ->order('weigh ASC')
            //->cache('menu-goods-' . $shop_id,10)
            ->select();

        if ($all) {
            $all = collection($all)->toArray();
        }
        $this->success('', $all);
    }

    /**
     * @ApiTitle    (广告图)
     * @ApiSummary  (广告图)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams   (name="shop_id", type=integer, required=true, description="商家id")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="integer", description="广告图id")
     * @ApiReturnParams  (name="name", type="string", description="图片")
     */
    public function ads()
    {
        $shopId = $this->request->post('shop_id', 0);
        $ads = (new Ads())
            ->where('switch', Ads::SWITCH_ON)
            ->where('shop_id = '.Ads::ALL_SHOP.' OR find_in_set(:shop_id,shop_id)', ['shop_id' => $shopId])
            ->order(['weigh' => 'desc'])
            ->cache('menu-ads-'. $shopId, 10)
            ->select();
        $this->success('', $ads);
    }

}
