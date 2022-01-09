<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/3/8
 * Time: 7:20 PM
 */


namespace addons\unidrink\model;

use think\Db;
use think\Exception;
use think\Model;

/**
 * 扩展用户表
 * Class UserExtend
 * @package addons\unidrink\model
 */
class UserExtend extends Model
{
    // 表名
    protected $name = 'unidrink_user_extend';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 关联基础用户表
     */
    public function user()
    {
        return $this->hasOne('user', 'id', 'user_id')
            ->field('id,avatar,mobile,username,level,score,money,gender,birthday');
    }

    /**
     * 用户的优惠券
     */
    public function couponUser()
    {
        return $this
            ->hasMany('couponUser', 'user_id', 'user_id')
            ->where('status', CouponUser::STATUS_OFF);
    }

    /**
     * 通过微信小程序openid获取用户id
     */
    public function getUserInfoByOpenid($openid)
    {
        $userExtend = $this
            ->with('user')
            ->withCount(['couponUser' => 'couponNum'])
            ->where(['openid' => $openid])
            ->find();
        if ($userExtend && $userExtend->user) {
            $user = $userExtend->user;
        } else {
            Db::startTrans();
            try {
                $params = [
                    'level'    => 1,
                    'score'    => 0,
                    'jointime'  => time(),
                    'joinip'    => $_SERVER['REMOTE_ADDR'],
                    'logintime' => time(),
                    'loginip'   => $_SERVER['REMOTE_ADDR'],
                    'prevtime'  => time(),
                    'status'    => 'normal',
                    'avatar'    => '',
                    'username'  => __('Tourist')
                ];
                $user = User::create($params, true);

                if ($userExtend) {
                    $userExtend->user_id = $user->id;
                } else {
                    self::create([
                        'user_id' => $user->id,
                        'openid' => $openid
                    ], true);
                }

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                return false;
            }
        }
        $user = $user->toArray();
        $user['currentValue'] = $userExtend['currentValue'];
        $user['couponNum'] = $userExtend['couponNum'];
        return $user;
    }


}
