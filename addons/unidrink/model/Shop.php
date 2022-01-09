<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/5/31
 * Time: 12:35 PM
 */


namespace addons\unidrink\model;


use think\Model;
use traits\model\SoftDelete;

class Shop extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'unidrink_shop';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 是否营业
    const STATUS_ON = 1; // 是
    const STATUS_OFF = 0; // 否

    // 外卖是否配送
    const NO_DELIVERY = 0; // 不配送

    protected $append = [
        'bussines_time',
    ];

    /**
     * 处理图片
     */
    public function getImageAttr($value) {
        return Config::getImagesFullUrl($value);
    }

    /**
     * 营业状态
     */
    public function getStatusTextAttr($value, $data)
    {
        $starttime = strtotime(date('1995-11-25 H:i:s', $data['starttime']));
        $endtime = strtotime(date('1995-11-25 H:i:s', $data['endtime']));
        $nowtime = strtotime(date('1995-11-25 H:i:s', time()));
        $text = '营业中';
        if ($data['status'] == self::STATUS_ON) {
            if (
                $nowtime < $starttime ||
                $nowtime > $endtime
            ) {
                $text = '已打烊';
            }
        } else {
            $text = '已打烊';
        }
        return $text;
    }

    /**
     * 营业状态码
     */
    public function getStatusAttr($value, $data)
    {
        $starttime = strtotime(date('1995-11-25 H:i:s', $data['starttime']));
        $endtime = strtotime(date('1995-11-25 H:i:s', $data['endtime']));
        $nowtime = strtotime(date('1995-11-25 H:i:s', time()));
        if ($value == self::STATUS_ON) {
            if (
                $nowtime < $starttime ||
                $nowtime > $endtime
            ) {
                $value = self::STATUS_OFF;
            }
        }
        return $value;
    }

    /**
     * 营业时间
     */
    public function getBussinesTimeAttr($value, $data)
    {
        if (!empty($data['starttime']) && !empty($data['endtime'])) {
            return date('H:i', $data['starttime']) . '-' . date('H:i', $data['endtime']);
        }
        return '';
    }

    /**
     * 配送距离
     */
    public function getDistanceAttr($value)
    {
        if (Config::getByName('take-out')['value'] == self::NO_DELIVERY) {
            return self::NO_DELIVERY;
        }
        return $value;
    }

    /**
     * 根据给定条件获取店铺列表
     */
    public function getList($lat = 0, $lng = 0, $page = 1, $pagesize = 10, $shop_id = 0, $keyword = '', $order = 'far')
    {
        $query = $this
            ->field("id,name,mobile,notice,min_price,delivery_price,lat,lng,distance,address,address_map,image,status,starttime,endtime,
            (6371 * acos (
               cos ( radians($lat) )
        * cos( radians( lat ) )
        * cos( radians( lng ) - radians($lng) )
        + sin ( radians($lat) ) * sin( radians( lat ) ))
            ) AS far");

        if ($shop_id != 0) {
            $query = $query->where('id = :shop_id', ['shop_id' => $shop_id]);
        }
        if ($keyword != '') {
            $query = $query->where("name LIKE :name", ['name' => '%'.$keyword.'%']);
        }

        $shop = $query
            ->fetchSql(false)
            ->order($order)
            ->page($page, $pagesize)
            ->select();

        return $shop;
    }

    /**
     * 根据当前管理员admin_id返回相关联的shop_id
     */
    public function getShopIdFromAdminId($adminId)
    {
        return $this->where('FIND_IN_SET('.$adminId.', admin_id)')->column('name', 'id');
    }
}
