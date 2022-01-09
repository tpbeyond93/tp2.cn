<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/7/15
 * Time: 10:42 PM
 */


namespace addons\unidrink\controller;


use addons\unidrink\extend\Common;
use addons\unidrink\model\CouponUser;
use think\Db;
use think\Exception;

/**
 * 优惠券接口
 * Class Coupon
 * @package addons\unidrink\controller
 */
class Coupon extends Base
{

    /**
     * @ApiTitle    (我的优惠券)
     * @ApiSummary  (我的优惠券)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="page", type=integer, required=true, description="页面")
     * @ApiParams   (name="pagesize", type=integer, required=true, description="页数")
     * @ApiParams   (name="shop_id", type=integer, required=false, description="店铺id")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="integer", description="优惠券id")
     * @ApiReturnParams  (name="shop_id", type="integer", description="可用店铺，0代表全部可用")
     * @ApiReturnParams  (name="title", type="string", description="优惠券名称")
     * @ApiReturnParams  (name="least", type="string", description="至少消费金额")
     * @ApiReturnParams  (name="value", type="string", description="优惠金额")
     * @ApiReturnParams  (name="starttime", type="integer", description="开始使用时间戳")
     * @ApiReturnParams  (name="starttime_text", type="integer", description="开始使用时间")
     * @ApiReturnParams  (name="endtime", type="integer", description="结束使用时间戳")
     * @ApiReturnParams  (name="endtime_text", type="integer", description="结束使用时间")
     * @ApiReturnParams  (name="type", type="integer", description="可用类型:0=通用,1=自取,2=外卖")
     * @ApiReturnParams  (name="score", type="integer", description="领优惠券消耗积分")
     * @ApiReturnParams  (name="instructions", type="string", description="使用说明")
     * @ApiReturnParams  (name="image", type="string", description="优惠券图片")
     * @ApiReturnParams  (name="status", type="integer", description="已使用:0=否,1=是")
     * @ApiReturnParams  (name="coupon_id", type="integer", description="源优惠券id")
     * @ApiReturnParams  (name="exchange_code", type="string", description="优惠券兑换码")
     * @ApiReturnParams  (name="my_receive", type="integer", description="我已领取多少张")
     * @ApiReturnParams  (name="shop_name", type="string", description="店铺名称")
     *
     */
    public function mine()
    {
        $page = $this->request->post('page', 1);
        $pageSize = $this->request->post('pagesize', 15);

        // 传了店铺id的话就只查这个店铺的
        $shopId = $this->request->post('shop_id', '');
        $shopId = intval($shopId);
        $time = time();
        //$andWhereShopId = empty($shopId) ? " AND shop_id = {$shopId} " : " AND ( shop_id = 0 OR FIND_IN_SET({$shopId}, shop_id) ) AND `status` = 0 AND starttime < {$time} AND endtime > {$time} ";
        $andWhereShopId = empty($shopId) ? " " : " AND ( shop_id = 0 OR FIND_IN_SET({$shopId}, shop_id) ) AND `status` = 0 ";

        // 传了类型的话就只查这个类型的
        $type = $this->request->post('type', '');
        $type = intval($type);
        $andWhereType = empty($type) ? " " : " AND `type` IN (0 ,{$type}) ";

        $prefix = \think\Config::get('database.prefix');
        $page = ($page - 1) * $pageSize;
        $where = "SELECT * FROM {$prefix}unidrink_coupon_user WHERE user_id = {$this->auth->id} {$andWhereShopId} {$andWhereType} AND starttime < {$time} AND endtime > {$time} ORDER BY FIELD(`status`,0,1),endtime DESC LIMIT {$page},{$pageSize}  ";
        //echo $where;exit;
        $list = Db::query($where);

        $shopId = '';
        foreach ($list as &$item) {
            $item['my_receive'] = 1;
            // 获取所有店铺的id
            $shopId .= $item['shop_id'] . ',';
            $item['starttime_text'] = date('Y-m-d', $item['starttime']);
            $item['endtime_text'] = date('Y-m-d', $item['endtime']);
            $item['createtime_text'] = date('Y-m-d H:i', $item['createtime']);
        }
        $shops = Common::getShopByIds($shopId);
        foreach ($list as $k => $v) {
            $list[$k]['shop_name'] = Common::getShopNameByIds($shops, $list[$k]['shop_id']);
        }

        $this->success('', $list);
    }

    /**
     * @ApiTitle    (优惠券可用数量)
     * @ApiSummary  (优惠券可用数量)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="shop_id", type=integer, required=false, description="店铺id")
     * @ApiReturn   ({"code":1,"msg":"","data":0})
     *
     */
    public function count()
    {
        // 传了店铺id的话就只查这个店铺的
        $shopId = $this->request->post('shop_id', '');
        $shopId = intval($shopId);
        $time = time();
        $andWhereShopId = empty($shopId) ? " " : " AND ( shop_id = 0 OR FIND_IN_SET({$shopId}, shop_id) ) AND `status` = 0 ";

        // 传了类型的话就只查这个类型的
        $type = $this->request->post('type', '');
        $type = intval($type);
        $andWhereType = empty($type) ? " " : " AND `type` IN (0 ,{$type}) ";

        $prefix = \think\Config::get('database.prefix');
        $where = "SELECT count(*) as count FROM {$prefix}unidrink_coupon_user WHERE user_id = {$this->auth->id} {$andWhereShopId} {$andWhereType} AND starttime < {$time} AND endtime > {$time} ";

        $result = Db::query($where);

        $this->success('', $result[0]['count'] ?? 0);
    }

    /**
     * @ApiTitle    (未领取优惠券)
     * @ApiSummary  (未领取优惠券)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="page", type=integer, required=true, description="页面")
     * @ApiParams   (name="pagesize", type=integer, required=true, description="页数")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="integer", description="优惠券id")
     * @ApiReturnParams  (name="shop_id", type="integer", description="可用店铺，0代表全部可用")
     * @ApiReturnParams  (name="title", type="string", description="优惠券名称")
     * @ApiReturnParams  (name="least", type="string", description="至少消费金额")
     * @ApiReturnParams  (name="value", type="string", description="优惠金额")
     * @ApiReturnParams  (name="starttime", type="integer", description="开始使用时间戳")
     * @ApiReturnParams  (name="starttime_text", type="integer", description="开始使用时间")
     * @ApiReturnParams  (name="endtime", type="integer", description="结束使用时间戳")
     * @ApiReturnParams  (name="endtime_text", type="integer", description="结束使用时间")
     * @ApiReturnParams  (name="type", type="integer", description="可用类型:0=通用,1=自取,2=外卖")
     * @ApiReturnParams  (name="score", type="integer", description="领优惠券消耗积分")
     * @ApiReturnParams  (name="instructions", type="string", description="使用说明")
     * @ApiReturnParams  (name="image", type="string", description="优惠券图片")
     * @ApiReturnParams  (name="status", type="integer", description="已使用:0=否,1=是")
     * @ApiReturnParams  (name="coupon_id", type="integer", description="源优惠券id")
     * @ApiReturnParams  (name="exchange_code", type="string", description="优惠券兑换码")
     * @ApiReturnParams  (name="my_receive", type="integer", description="我已领取多少张")
     * @ApiReturnParams  (name="shop_name", type="string", description="店铺名称")
     *
     */
    public function index()
    {
        $page = $this->request->post('page', 1);
        $pageSize = $this->request->post('pagesize', 15);
        $score = $this->request->post('score', 0);

        // 已领取的优惠券
        $iHaveCoupon = (new CouponUser())
            ->where(['user_id' => $this->auth->id])
            ->where('endtime > ' . time())
            ->group('coupon_id')
            ->column('count(id)', 'coupon_id');

        $whereOr = [];
        foreach ($iHaveCoupon as $key => $value) {
            $whereOr[] = " IF(id = {$key},`limit` > {$value}, 1) ";
        }
        $where = $whereOr ? ' ( ' . implode('AND', $whereOr) . ' ) ' : '';

        $list = (new \addons\unidrink\model\Coupon())
            ->where('distribute > receive and endtime > ' . time())
            ->where($where)
            ->where('score', '>=', $score)
            ->order('weigh desc')
            ->page($page, $pageSize)
            ->select();

        $shopId = '';
        foreach ($list as &$item) {
            if (isset($iHaveCoupon[$item['id']])) {
                // 我领取了的数量
                $item['my_receive'] = $iHaveCoupon[$item['id']];
            } else {
                $item['my_receive'] = 0;
            }
            // 获取所有店铺的id
            $shopId .= $item['shop_id'] . ',';
        }
        $shops = Common::getShopByIds($shopId);
        foreach ($list as $k => $v) {
            $list[$k]['shop_name'] = Common::getShopNameByIds($shops, $list[$k]['shop_id']);
        }

        $this->success('', $list);
    }

    /**
     * @ApiTitle    (兑换记录)
     * @ApiSummary  (兑换记录)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="page", type=integer, required=true, description="页面")
     * @ApiParams   (name="pagesize", type=integer, required=true, description="页数")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="integer", description="优惠券id")
     * @ApiReturnParams  (name="shop_id", type="integer", description="可用店铺，0代表全部可用")
     * @ApiReturnParams  (name="title", type="string", description="优惠券名称")
     * @ApiReturnParams  (name="least", type="string", description="至少消费金额")
     * @ApiReturnParams  (name="value", type="string", description="优惠金额")
     * @ApiReturnParams  (name="starttime", type="integer", description="开始使用时间戳")
     * @ApiReturnParams  (name="starttime_text", type="integer", description="开始使用时间")
     * @ApiReturnParams  (name="endtime", type="integer", description="结束使用时间戳")
     * @ApiReturnParams  (name="endtime_text", type="integer", description="结束使用时间")
     * @ApiReturnParams  (name="type", type="integer", description="可用类型:0=通用,1=自取,2=外卖")
     * @ApiReturnParams  (name="score", type="integer", description="领优惠券消耗积分")
     * @ApiReturnParams  (name="instructions", type="string", description="使用说明")
     * @ApiReturnParams  (name="image", type="string", description="优惠券图片")
     * @ApiReturnParams  (name="status", type="integer", description="已使用:0=否,1=是")
     * @ApiReturnParams  (name="coupon_id", type="integer", description="源优惠券id")
     * @ApiReturnParams  (name="exchange_code", type="string", description="优惠券兑换码")
     * @ApiReturnParams  (name="my_receive", type="integer", description="我已领取多少张")
     * @ApiReturnParams  (name="shop_name", type="string", description="店铺名称")
     *
     */
    public function exchangeLog()
    {
        $page = $this->request->post('page', 1);
        $pageSize = $this->request->post('pagesize', 15);

        $couponUserModel = new CouponUser();
        $list = $couponUserModel
            ->where("user_id = {$this->auth->id} and exchange_code IS NOT NULL")
            ->page($page, $pageSize)
            ->select();

        $shopId = '';
        foreach ($list as &$item) {
            $item['my_receive'] = 1;
            // 获取所有店铺的id
            $shopId .= $item['shop_id'] . ',';
        }
        $shops = Common::getShopByIds($shopId);
        foreach ($list as $k => $v) {
            $list[$k]['shop_name'] = Common::getShopNameByIds($shops, $list[$k]['shop_id']);
        }

        $this->success('', $list);
    }

    /**
     * @ApiTitle    (领取/兑换优惠券)
     * @ApiSummary  (优惠券id或兑换码二传一)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="id", type=integer, required=true, description="优惠券id")
     * @ApiParams   (name="code", type=string, required=true, description="兑换码")
     * @ApiReturn   ({"code":1,"msg":"","data":true})
     *
     */
    public function receive()
    {
        $id = $this->request->post('id');
        $code = $this->request->post('code');

        try {
            Db::startTrans();
            if ($id) {
                $coupon = \addons\unidrink\model\Coupon::get($id);
            } else {
                $coupon = \addons\unidrink\model\Coupon::getByExchangeCode($code);
            }
            if (empty($coupon)) {
                $this->error('优惠券不存在');
            }
            $count = (new CouponUser())->where(['user_id' => $this->auth->id, 'coupon_id' => $coupon->id])->count();
            if ($count >= $coupon->limit) {
                $this->error('该优惠券每人只能领取' . $coupon->limit . '张');
            }
            if (!$coupon) {
                $this->error('优惠券不存在');
            }
            if ($coupon->receive >= $coupon->distribute) {
                $this->error('此优惠券已领完');
            }
            if ($coupon->score > $this->auth->score) {
                $this->error("此优惠券需要{$coupon->score}积分，积分不足");
            }

            // 更新优惠券
            // 这里用乐观锁
            $result = \addons\unidrink\model\Coupon::update(
                ['receive' => $coupon->receive + 1],
                ['id' => $coupon->id, 'updatetime' => $coupon->updatetime]
            );

            if ($result) {
                // 减积分
                \app\common\model\User::score(-$coupon->score, $this->auth->id, "优惠券兑换积分");

                CouponUser::create([
                    'user_id' => $this->auth->id,
                    'coupon_id' => $coupon->id,
                    'title' => $coupon->title,
                    'value' => $coupon->value,
                    'instructions' => $coupon->instructions,
                    'type' => $coupon->type,
                    'image' => $coupon->image,
                    'shop_id' => $coupon->shop_id,
                    'starttime' => $coupon->starttime,
                    'score' => $coupon->score,
                    'least' => $coupon->least,
                    'endtime' => $coupon->endtime,
                    'exchange_code' => $code
                ]);
                Db::commit();
            } else {
                Db::rollback();
                $this->error('请求繁忙');
            }

        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('', true);
    }
}
