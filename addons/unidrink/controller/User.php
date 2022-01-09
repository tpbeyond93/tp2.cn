<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2019/10/25
 * Time: 11:09 下午
 */


namespace addons\unidrink\controller;

use addons\unidrink\extend\Wechat;
use addons\unidrink\model\CouponUser;
use addons\unidrink\model\UserExtend;
use app\common\library\Sms;
use think\Cache;
use think\Session;
use think\Validate;

/**
 * 用户接口
 * Class User
 * @package addons\unidrink\controller
 */
class User extends Base
{
    protected $noNeedLogin = ['login', 'status', 'authSession', 'decryptData', 'resetpwd', 'loginForWechatMini'];

    /**
     * @ApiTitle    (会员登录)
     * @ApiSummary  (密码和验证码二传一)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams   (name="mobile", type="string", required=true, description="电话号码")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiParams   (name="captcha", type="string", required=true, description="验证码")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     * @ApiReturnParams  (name="id", type="integer", description="用户id")
     * @ApiReturnParams  (name="user_id", type="integer", description="用户id")
     * @ApiReturnParams  (name="username", type="string", description="用户名")
     * @ApiReturnParams  (name="mobile", type="string", description="用户手机号")
     * @ApiReturnParams  (name="avatar", type="string", description="用户头像")
     * @ApiReturnParams  (name="score", type="integer", description="积分")
     * @ApiReturnParams  (name="token", type="integer", description="登录token")
     * @ApiReturnParams  (name="expirestime", type="integer", description="登录过期时间")
     * @ApiReturnParams  (name="expires_in", type="integer", description="登录有限期限")
     */
    public function login()
    {
        $mobile = $this->request->post('mobile');
        $password = $this->request->post('password');
        $captcha = $this->request->post('captcha');

        if (!$mobile) {
            $this->error('手机号不能为空');
        }
        if (!$password && !$captcha) {
            $this->error('参数错误');
        }
        if ($password) {
            $ret = $this->auth->login($mobile, $password);
        } else {
            $ret = Sms::check($mobile, $captcha, 'register');
            if (!$ret) {
                $this->error('验证码错误');
            }
            Sms::flush($mobile, 'register');

            $user = \app\common\model\User::getByMobile($mobile);
            if ($user) {
                // 存在用户就直接登录
                $this->auth->direct($user->id);
            } else {
                // 不存在就注册
                $avatar = \addons\unidrink\model\Config::getByName('avatar')['value'] ?? '';
                $ret = $this->auth->register('新用户', md5(time()), '', $mobile, ['avatar' => $avatar]);
            }

        }
        if ($ret) {
            $data = $this->auth->getUserinfo();
            $data['avatar'] = \addons\unidrink\model\Config::getImagesFullUrl($data['avatar']);
            $this->success('登录成功', $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * @ApiTitle    (重置密码)
     * @ApiSummary  (重置密码)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams   (name="mobile", type="string", required=true, description="电话号码")
     * @ApiParams   (name="newpassword", type="string", required=true, description="新密码")
     * @ApiParams   (name="captcha", type="string", required=true, description="验证码")
     * @ApiReturn   ({"code":1,"msg":"","data":true})
     */
    public function resetpwd()
    {
        $mobile = $this->request->post("mobile");

        $newpassword = $this->request->post("password");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error('参数错误');
        }

        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error('手机号码错误');
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if (!$user) {
            $this->error('手机号码错误');
        }
        $ret = Sms::check($mobile, $captcha, 'resetpwd');
        if (!$ret) {
            $this->error('验证码错误');
        }
        Sms::flush($mobile, 'resetpwd');

        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success('', true);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * @ApiTitle    (更改用户信息)
     * @ApiSummary  (更改用户信息)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams   (name="username", type="string", required=true, description="用户名")
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="avatar", type="string", required=true, description="头像")
     * @ApiParams   (name="gender", type="string", required=true, description="性别")
     * @ApiParams   (name="birthday", type="string", required=true, description="生日日期")
     * @ApiReturn   ({"code":1,"msg":"","data":1})
     */
    public function edit()
    {
        $userInfo = $this->auth->getUserinfo();
        $username = $this->request->post('username', $userInfo['username']);
        $mobile = $this->request->post('mobile', $userInfo['mobile']);
        $avatar = $this->request->post('avatar', $userInfo['avatar']);
        $gender = $this->request->post('gender', 0);
        $birthday = $this->request->post('birthday');

        $user = \app\common\model\User::get($this->auth->id);
        $user->username = $username;
        $user->mobile = $mobile;
        $user->avatar = $avatar;
        $user->gender = $gender;
        $user->birthday = $birthday;

        if ($user->save()) {
            $this->success(__('Modified'), 1);
        } else {
            $this->error(__('Fail'), 0);
        }
    }

    /**
     * @ApiTitle    (登录状态)
     * @ApiSummary  (登录状态)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiReturn   ({"code":1,"msg":"","data":true})
     */
    public function status()
    {
        $this->success('', $this->auth->isLogin());
    }

    /**
     * @ApiTitle    (微信小程序登录)
     * @ApiSummary  (微信小程序登录)
     * @ApiMethod   (GET)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiParams   (name="code", type="string", required=true, description="小程序调用wx.login返回的code")
     * @ApiReturn   ({"code":1,"msg":"","data":{}})
     *
     * @ApiReturnParams  (name="openid", type="integer", description="微信用户openid")
     * @ApiReturnParams  (name="userInfo.id", type="integer", description="用户id")
     * @ApiReturnParams  (name="userInfo.username", type="string", description="用户名称")
     * @ApiReturnParams  (name="userInfo.mobile", type="string", description="用户电话")
     * @ApiReturnParams  (name="userInfo.avatar", type="string", description="用户头像")
     * @ApiReturnParams  (name="userInfo.score", type="string", description="用户积分")
     * @ApiReturnParams  (name="userInfo.token", type="string", description="用户登录token")
     *
     */
    public function authSession()
    {
        $platform = $this->request->header('platform');
        switch ($platform) {
            case 'MP-WEIXIN':
                $code = $this->request->get('code');
                $data = Wechat::authSession($code);

                // 如果有手机号码，自动登录
                if (isset($data['userInfo']['mobile']) && (!empty($data['userInfo']['mobile']) || $data['userInfo']['mobile'] != '')) {
                    $this->auth->direct($data['userInfo']['id']);
                    if ($this->auth->isLogin()) {
                        $data['userInfo']['token'] = $this->auth->getToken();
                        // 支付的时候用
                        Cache::set('openid_' . $data['userInfo']['id'], $data['openid'], 7200);
                    }
                }

                break;
            default:
                $data = [];
        }
        $this->success('', $data);
    }


    /**
     * @ApiTitle    (微信小程序消息解密)
     * @ApiSummary  (微信小程序消息解密，必须先调用authSession获取到session_key)
     * @ApiMethod   (POST)
     * @ApiParams   (name="iv", type="string", required=true, description="")
     * @ApiParams   (name="encryptedData", type="string", required=true, description="")
     * @ApiReturn   ({"code":1,"msg":"","data":{手机号码，用户信息等等，具体看用户授权什么权限}})
     *
     */
    public function decryptData()
    {
        $iv = $this->request->post('iv');

        $encryptedData = $this->request->post('encryptedData');

        $app = Wechat::initEasyWechat('miniProgram');

        $decryptedData = $app->encryptor->decryptData(Session::get('session_key'), $iv, $encryptedData);

        $this->success('', $decryptedData);
    }

    /**
     * @ApiTitle    (微信小程序通过授权手机号登录)
     * @ApiSummary  (微信小程序通过授权手机号登录)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiParams   (name="iv", type="string", required=true, description="")
     * @ApiParams   (name="encryptedData", type="string", required=true, description="")
     * @ApiReturn   ({"code":1,"msg":"","data":{}})
     *
     * @ApiReturnParams  (name="id", type="integer", description="用户id")
     * @ApiReturnParams  (name="openid", type="integer", description="微信用户openid")
     * @ApiReturnParams  (name="mobile", type="string", description="用户电话")
     * @ApiReturnParams  (name="avatar", type="string", description="用户头像")
     * @ApiReturnParams  (name="username", type="string", description="用户名称")
     * @ApiReturnParams  (name="level", type="string", description="用户级别")
     * @ApiReturnParams  (name="score", type="string", description="用户积分")
     * @ApiReturnParams  (name="money", type="string", description="用户余额")
     * @ApiReturnParams  (name="currentValue", type="string", description="已消费数额")
     * @ApiReturnParams  (name="couponNum", type="string", description="优惠券数量")
     * @ApiReturnParams  (name="gender", type="string", description="性别")
     * @ApiReturnParams  (name="birthday", type="string", description="生日")
     * @ApiReturnParams  (name="jointime", type="string", description="加入时间")
     * @ApiReturnParams  (name="token", type="string", description="用户登录token")
     *
     */
    public function loginForWechatMini()
    {
        $iv = $this->request->post('iv');

        $encryptedData = $this->request->post('encryptedData');

        $app = Wechat::initEasyWechat('miniProgram');

        $decryptedData = $app->encryptor->decryptData(Session::get('session_key'), $iv, $encryptedData);

        if (isset($decryptedData['phoneNumber'])) {
            $openid = Session::get('openid');

            // 看看有没有这个mobile的用户
            $user = \addons\unidrink\model\User::getByMobile($decryptedData['phoneNumber']);
            if ($user) {
                // 有 处理：1，把；user_extend对应的user删除；2，把user_extend表的user_id字段换成已存在的用户id
                $userExtend = UserExtend::getByOpenid($openid);
                if ($userExtend) {
                    if ($userExtend['user_id'] != $user->id) {
                        \addons\unidrink\model\User::destroy($userExtend['user_id']);
                        $userExtend->user_id = $user->id;
                        $userExtend->save();
                    }
                } else {
                    UserExtend::create(['user_id' => $user->id, 'openid' => $openid]);
                }
            } else {
                // 没有
                $userExtend = UserExtend::getByOpenid($openid);
                if ($userExtend) {
                    $user = \addons\unidrink\model\User::get($userExtend->user_id);
                    $user->mobile = $decryptedData['phoneNumber'];
                    $user->save();
                } else {
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
                        'username'  => __('Tourist'),
                        'mobile'    => $decryptedData['phoneNumber'],
                        'money'    => 0
                    ];
                    $user = \addons\unidrink\model\User::create($params, true);
                    UserExtend::create(['user_id' => $user->id, 'openid' => $openid]);
                }
            }

            $userInfo['id'] = $user->id;
            $userInfo['openid'] = $openid;
            $userInfo['mobile'] = $user->mobile;
            $userInfo['avatar'] = \addons\unidrink\model\Config::getImagesFullUrl($user->avatar);
            $userInfo['username'] = $user->username;
            $userInfo['level'] = $user->level;
            $userInfo['score'] = $user->score;
            $userInfo['money'] = $user->money ?? 0;
            $userInfo['currentValue'] = $userExtend->currentValue ?? 0;
            $userInfo['couponNum'] = 0;
            $userInfo['gender'] = $user->gender ?? 2;
            $userInfo['birthday'] = $user->birthday ?? date('Y-m-d H:i:s');
            $userInfo['jointime'] = date('Y-m-d H:i:s', $user->jointime);

            $this->auth->direct($userInfo['id']);
            if ($this->auth->isLogin()) {
                $userInfo['token'] = $this->auth->getToken();
                // 支付的时候用
                Cache::set('openid_' . $userInfo['id'], $openid, 7200);
            }

            $this->success('', $userInfo);

        } else {
            $this->error(__('Logged in failed'));
        }

    }

    /**
     * @ApiTitle    (获取用户基础信息)
     * @ApiSummary  (获取用户基础信息)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiReturn   ({"code":1,"msg":"","data":{}})
     *
     * @ApiReturnParams  (name="id", type="integer", description="用户id")
     * @ApiReturnParams  (name="openid", type="integer", description="微信用户openid")
     * @ApiReturnParams  (name="mobile", type="string", description="用户电话")
     * @ApiReturnParams  (name="avatar", type="string", description="用户头像")
     * @ApiReturnParams  (name="username", type="string", description="用户名称")
     * @ApiReturnParams  (name="level", type="string", description="用户级别")
     * @ApiReturnParams  (name="score", type="string", description="用户积分")
     * @ApiReturnParams  (name="money", type="string", description="用户余额")
     * @ApiReturnParams  (name="currentValue", type="string", description="已消费数额")
     * @ApiReturnParams  (name="couponNum", type="string", description="优惠券数量")
     * @ApiReturnParams  (name="gender", type="string", description="性别")
     * @ApiReturnParams  (name="birthday", type="string", description="生日")
     * @ApiReturnParams  (name="jointime", type="string", description="加入时间")
     *
     */
    public function getUserInfo()
    {
        $user = (new \addons\unidrink\model\User)
            ->with(['extend'])
            ->where(['id' => $this->auth->id])
            ->find();

        $userInfo['id'] = $user->id;
        $userInfo['openid'] = $user->extend->openid ?? '';
        $userInfo['mobile'] = $user->mobile;
        $userInfo['avatar'] = \addons\unidrink\model\Config::getImagesFullUrl($user->avatar);
        $userInfo['username'] = $user->username;
        $userInfo['level'] = $user->level;
        $userInfo['score'] = $user->score;
        $userInfo['money'] = $user->money;
        $userInfo['currentValue'] = $user->extend->currentValue ?? 0;
        $userInfo['couponNum'] = (new CouponUser)->where(['user_id' => $user->id, 'status' => CouponUser::STATUS_OFF])->count();
        $userInfo['gender'] = $user->gender;
        $userInfo['birthday'] = $user->birthday;
        $userInfo['token'] = $this->auth->getToken();
        $userInfo['jointime'] = date('Y-m-d H:i:s', $user->jointime);

        $this->success('', $userInfo);
    }

    /**
     * 获取会员码，有效期30秒
     * @ApiInternal
     */
    public function memberCode() {

    }
}
