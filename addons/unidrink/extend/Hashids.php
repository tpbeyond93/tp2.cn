<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/1/7
 * Time: 9:05 下午
 */


namespace addons\unidrink\extend;

use addons\unidrink\model\Config;

class Hashids
{
    private static $hashids;

    /**
     * 单列模型实例化
     * @param $salt
     * @param $hashLength
     * @return \Hashids\Hashids
     */
    public static function getInstanceHashids($salt, $hashLength)
    {
        if (!self::$hashids instanceof \Hashids\Hashids) {
            self::$hashids =  new \Hashids\Hashids($salt, $hashLength);
        }
        return self::$hashids;
    }

    public static function encodeHex($str, $hashLength = 5)
    {
        $salt = Config::getByName('salt')['value'];

        $hashids = self::getInstanceHashids($salt, $hashLength);

        return $hashids->encodeHex($str);
    }

    public static function decodeHex($str, $hashLength = 5)
    {
        $salt = Config::getByName('salt')['value'];

        $hashids = self::getInstanceHashids($salt, $hashLength);

        return $hashids->decodeHex($str);
    }

}
