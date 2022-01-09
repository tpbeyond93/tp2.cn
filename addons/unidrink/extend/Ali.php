<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/5/8
 * Time: 10:45 AM
 */


namespace addons\unidrink\extend;


use addons\unidrink\model\Config;
use Yansongda\Pay\Pay;

class Ali
{
    public static function initAliPay()
    {
        $config = [
            'app_id' => Config::getByName('ali_app_id')['value'],
            'notify_url' => Config::getByName('ali_notify_url')['value'],
            'return_url' => Config::getByName('ali_return_url')['value'],
            'ali_public_key' => Config::getByName('ali_public_key')['value'],
            // 加密方式： **RSA2**
            'private_key' => Config::getByName('ali_private_key')['value'],
//            'log' => [ // optional
//                'file' => './logs/alipay.log',
//                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
//                'type' => 'single', // optional, 可选 daily.
//                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
//            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
            ],
            //'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
        ];

        if (Config::getByName('ali_sandbox')['value'] == 1) {
            $config['mode'] = 'dev';
        }

        return Pay::alipay($config);
    }
}
