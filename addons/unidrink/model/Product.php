<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2019/11/10
 * Time: 11:45 上午
 */


namespace addons\unidrink\model;


use think\Exception;
use think\Model;
use traits\model\SoftDelete;

/**
 * 商品模型
 * Class Product
 * @package addons\unidrink\model
 */
class Product extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'unidrink_product';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 是否上架？
    const SWITCH_ON = 1; //是
    const SWITCH_OFF = 0; //否

    // 是否开启规格？
    const SPEC_ON = 1; //是
    const SPEC_OFF = 0; //否

    // 代表全部店铺
    const ALL_SHOP = 0;

    // 隐藏属性
    protected $hidden = [
        'real_look',
        'real_sales',
        //'specList',
        //'specTableList',
    ];

    protected $append = [
        //'specChildList'
    ];

    /**
     * 处理图片
     * @param $value
     * @return string
     */
    public function getImageAttr($value) {
        return Config::getImagesFullUrl($value);
    }

    /**
     * 获取销售量
     * @param $value
     * @param $data
     */
    public function getSalesAttr($value, $data) {
        return $data['sales'] + $data['real_sales'];
    }

    /**
     * 处理图片
     * @param $value
     * @param $data
     * @return string
     */
    public function getImagesTextAttr($value, $data){
        $images = explode(',', $data['images']);
        foreach ($images as &$image) {
            $image = Config::getImagesFullUrl($image);
        }
        return $images;
    }

    /**
     * 处理规格属性
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getSpecListAttr($value, $data) {
        $specList = !empty($data['specList']) ? json_decode($data['specList'], true) : [];
        if (empty($specList)) {
            return [];
        }
        foreach ($specList as &$value) {
            //$value['disable'] = []; // 不可选值，如果库存为空就是不可选
            if (isset($value['child'][0]))
                $value['default'] = $value['child'][0]; // 默认值
        }
        return $specList;
    }

    /**
     * 给前端用
     */
//    public function getSpecChildListAttr($value, $data) {
//        if ($data['use_spec'] == self::SPEC_OFF) {
//            return [];
//        }
//
//        $specList = !empty($data['specList']) ? json_decode($data['specList'], true) : [];
//        if (empty($specList)) {
//            return [];
//        }
//
//        $e = 1;
//        $ee = 1;
//        $specChildList = [];
//        foreach ($specList as $i => $v) {
//            $specList[$i]['id'] = $e++;
//            foreach ($specList[$i]['child'] as $ii => $vv) {
//                array_push($specChildList, [
//                    'id' => $ee++,
//                    'pid' => $specList[$i]['id'],
//                    'name' => $specList[$i]['child'][$ii],
//                    'is_default' => $ii === 0 ? 1 : 0
//                ]);
//            }
//        }
//
//        return $specChildList;
//    }

    /**
     * 处理规格值
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getSpecTableListAttr($value, $data) {
        $specs = !empty($data['specTableList']) ? json_decode($data['specTableList'], true) : [];
        foreach ($specs as &$spec) {
            $spec['image'] = Config::getImagesFullUrl($spec['image']);
        }
        return $specs;
    }

    /**
     * 获取创建订单需要的产品信息
     * @param string $spec
     * @param int $number
     * @return array
     * @throws Exception
     */
    public function getDataOnCreateOrder(string $spec = '', $number = 1)
    {
        $data = (new \addons\unidrink\extend\Product)->getBaseData($this->getData(), $spec);
        if ($data['stock'] < 1) {
            throw new Exception('产品库存不足');
        }
        $product = $this->getData();
        $data['title'] = $product['title'];
        $data['spec'] = $spec;
        $data['number'] = $number;
        $data['id'] = $product['id'];

        return $data;
    }

}
