<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/3/7
 * Time: 4:09 PM
 */


namespace addons\unidrink\extend;

use addons\unidrink\model\Config;
use addons\unidrink\model\Order;
use addons\unidrink\model\UserExtend;
use EasyWeChat\Factory;
use think\Cache;
use think\Session;

class Wechat
{
    public static function initEasyWechat($type = 'miniProgram')
    {
        $config = [
            // 必要配置
            'app_id' => Config::getByName('app_id')['value'],
            'secret' => Config::getByName('secret')['value'],

            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            //'response_type' => 'array',
//            'log' => [
//                'level' => 'debug',
//                'file' => __DIR__.'/wechat.log',
//            ],
        ];

        switch ($type) {
            case 'miniProgram':
                return Factory::miniProgram($config);
                break;
            case 'payment':
                $config['mch_id'] = Config::getByName('mch_id')['value'];
                $config['key'] = Config::getByName('key')['value'];
                // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
                $config['cert_path'] = Config::getByName('cert_path')['value']; // XXX: 绝对路径！！！！
                $config['key_path'] = Config::getByName('key_path')['value'];      // XXX: 绝对路径！！！！
                $config['notify_url'] = Config::getByName('notify_url')['value'];;     // 你也可以在下单时单独设置来想覆盖它
                return Factory::payment($config);
                break;
        }
    }

    /**
     * 小程序登录
     */
    public static function authSession($code)
    {
        $app = self::initEasyWechat('miniProgram');

        $result = $app->auth->session($code);

        if (isset($result['session_key']) && isset($result['openid'])) {
            //储存session_key 用来解密微信用户授权数据
            Session::set('session_key', $result['session_key']);
            Session::set('openid', $result['openid']);
            $result['userInfo'] = (new UserExtend())->getUserInfoByOpenid($result['openid']);
            $result['userInfo']['openid'] = $result['openid'];
            unset($result['session_key']);
        }

        return $result;
    }

    /**
     * 根据user_id获取用户Openid
     * @param $userId
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOpenidByUserId($userId)
    {
        $openid = Cache::get('openid_' . $userId);
        if (empty($openid)) {
            $userExtend = (new UserExtend())->where(['user_id' => $userId])->field('openid')->find();
            if (empty($userExtend['openid'])) {
                return false;
            }
            $openid = $userExtend['openid'];
            Cache::set('openid_' . $userId, $openid, 7200);
        }
        return $openid;
    }

    /**
     * 小程序调起支付数据签名
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=7_7&index=5
     * @param array $params
     * @param string $key
     * @return string
     */
    public static function paySign($params, $key)
    {
        ksort($params);
        $string = "";
        foreach ($params as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $string .= $k . "=" . $v . "&";
            }
        }
        $string = $string . "key=" . $key;
        //$String= "appId=xxxxx&nonceStr=xxxxx&package=prepay_id="xxxxx&signType=MD5&timeStamp=xxxxx&key=xxxxx"
        return strtoupper(md5($string));
    }

    /**
     * 判断H5页面是否在微信内
     */
    public static function h5InWechat()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }

    /**
     * 获取微信支付参数trade_type
     * @param string $platfrom
     * @return string
     */
    public static function getTradeType(string $platfrom = 'MP-WEIXIN')
    {
        $trade_type = 'JSAPI';
        switch ($platfrom) {
            case 'MP-WEIXIN':
                $trade_type = 'JSAPI';
                break;
            case 'H5':
                $trade_type = 'MWEB';
                break;
            case 'APP-PLUS':
                $trade_type = 'APP';
                break;
        }

        // 如果是微信内访问 公众号等
        if (self::h5InWechat()) {
            $trade_type = 'JSAPI';
        }
        return $trade_type;
    }

    /**
     * 处理微信小程序订阅消息字段格式
     * @param array $template 模板
     * @param array $vals 模板值
     * @return array
     */
    public static function subscribeMsgData(array $template, array $vals): array
    {
        $data = [];
        foreach ($vals as $key => $val) {
            if (!empty($template[$key])) {
                $data[$template[$key]] = [
                    'value' => $val
                ];
            }
        }
        // 格式
//        [
//            'phrase1' => [
//                'value' => 1, // 审核结果
//            ],
//            'thing2' => [
//                'value' => 11, // 认证内容
//            ],
//            'date3' => [
//                'value' => 111, // 时间
//            ]
//        ];

        //thing.DATA	事物	20个以内字符	可汉字、数字、字母或符号组合
        //number.DATA	数字	32位以内数字	只能数字，可带小数
        //letter.DATA	字母	32位以内字母	只能字母
        //symbol.DATA	符号	5位以内符号	只能符号
        //character_string.DATA	字符串	32位以内数字、字母或符号	可数字、字母或符号组合
        //time.DATA	时间	24小时制时间格式（支持+年月日）	例如：15:01，或：2019年10月1日 15:01
        //date.DATA	日期	年月日格式（支持+24小时制时间）	例如：2019年10月1日，或：2019年10月1日 15:01
        //amount.DATA	金额	1个币种符号+10位以内纯数字，可带小数，结尾可带“元”	可带小数
        //phone_number.DATA	电话	17位以内，数字、符号	电话号码，例：+86-0766-66888866
        //car_number.DATA	车牌	8位以内，第一位与最后一位可为汉字，其余为字母或数字	车牌号码：粤A8Z888挂
        //name.DATA	姓名	10个以内纯汉字或20个以内纯字母或符号	中文名10个汉字内；纯英文名20个字母内；中文和字母混合按中文名算，10个字内
        //phrase.DATA	汉字	5个以内汉字	5个以内纯汉字，例如：配送中
        foreach ($data as $key => &$value) {
            switch (preg_replace("/\\d+/", '', $key)) {
                case 'thing':
                    $value['value'] = mb_substr($value['value'], 0, 20);
                    break;
                case 'number':
                    $value['value'] = substr($value['value'], 0, 32);
                    break;
                case 'letter':
                    $value['value'] = substr($value['value'], 0, 32);
                    break;
                case 'symbol':
                    $value['value'] = substr($value['value'], 0, 5);
                    break;
                case 'character_string':
                    $value['value'] = substr($value['value'], 0, 32);
                    break;
                case 'time':
                    // 让微信服务器判断
                    break;
                case 'date':
                    // 让微信服务器判断
                    break;
                case 'amount':
                    // 让微信服务器判断
                    break;
                case 'phone_number':
                    // 让微信服务器判断
                    break;
                case 'car_number':
                    $value['value'] = mb_substr($value['value'], 0, 8);
                    break;
                case 'name':
                    if (strlen($value['value']) > 20) {
                        $value['value'] = substr($value['value'], 0, 20);
                    }
                    if (strlen($value['value']) != mb_strlen($value['value']) && mb_strlen($value['value']) > 10) {
                        $value['value'] = mb_substr($value['value'], 0, 10);
                    }
                    break;
                case 'phrase':
                    $value['value'] = mb_substr($value['value'], 0, 5);
                    break;
            }
        }

        return $data;
    }

    /**
     * 根据订单类型返回模板信息 - 下单时
     * @param int $type
     */
    public static function getOrderSubMsgFromType(int $type = Order::BUY_TYPE_TAKEIN): array
    {
        if ($type == Order::BUY_TYPE_TAKEIN) {
            $data['template_id'] = Config::getByName('takein')['value'];
            $data['template_data'] = json_decode(Config::getByName('takein_data')['value'], true);
        } else {
            $data['template_id'] = Config::getByName('takeout')['value'];
            $data['template_data'] = json_decode(Config::getByName('takeout_data')['value'], true);
        }
        return $data;
    }

    /**
     * 根据订单类型返回模板信息 - 出单时
     * @param int $type
     */
    public static function getMadeSubMsgFromType(int $type = Order::BUY_TYPE_TAKEIN): array
    {
        if ($type == Order::BUY_TYPE_TAKEIN) {
            $data['template_id'] = Config::getByName('takein_made')['value'];
            $data['template_data'] = json_decode(Config::getByName('takein_made_data')['value'], true);
        } else {
            $data['template_id'] = Config::getByName('takeout_made')['value'];
            $data['template_data'] = json_decode(Config::getByName('takeout_made_data')['value'], true);
        }
        return $data;
    }
}
