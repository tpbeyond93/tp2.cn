<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/4/27
 * Time: 12:45 PM
 */

namespace addons\unidrink\controller;

use addons\unidrink\model\Config;
use app\common\library\Sms as Smslib;
use addons\unidrink\model\User;

/**
 * 信息接口
 */
class Sms extends Base
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     * @ApiTitle    (发送验证码)
     * @ApiSummary  (发送验证码)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="event", type="string", required=true, description="动作:register=注册,resetpwd=重置密码")
     * @ApiReturn   ({"code":1,"msg":"发送成功","data":1})
     *
     */
    public function send()
    {
        $mobile = $this->request->post("mobile");
        $event = $this->request->post("event");
        $event = $event ? $event : 'register';

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        $last = Smslib::get($mobile, $event);
        if ($last && time() - $last['createtime'] < 60) {
            $this->error(__('发送频繁'));
        }
        $ipSendTotal = \app\common\model\Sms::where(['ip' => $this->request->ip()])->whereTime('createtime', '-1 hours')->count();
        if ($ipSendTotal >= 5) {
            $this->error(__('发送频繁'));
        }
        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                // 已被注册 就直接登录
                //$this->error(__('手机号已被注册'));
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('手机号已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Smslib::send($mobile, null, $event);
        if ($ret) {
            $this->success(__('发送成功'), true);
        } else {
            $this->error(__('发送失败'), false);
        }
    }

    /**
     * @ApiTitle    (微信小程序订阅信息)
     * @ApiSummary  (微信小程序订阅信息)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiReturn   ({"code":1,"msg":"发送成功","data":{}})
     * @ApiReturnParams  (name="takein", type="string", description="堂食-点餐")
     * @ApiReturnParams  (name="takeout", type="string", description="外卖-点餐")
     * @ApiReturnParams  (name="takein_made", type="string", description="堂食-出单")
     * @ApiReturnParams  (name="takeout_made", type="string", description="外卖-配送")
     */
    public function subscribeMsg()
    {
        $config['takein'] = Config::getByName('takein')['value'];
        $config['takeout'] = Config::getByName('takeout')['value'];
        $config['takein_made'] = Config::getByName('takein_made')['value'];
        $config['takeout_made'] = Config::getByName('takeout_made')['value'];
        $this->success('', $config);
    }
}
