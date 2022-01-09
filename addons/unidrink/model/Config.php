<?php

namespace addons\unidrink\model;


use think\Cache;
use think\Model;

class Config extends Model
{
    // 表名
    protected $name = 'unidrink_config';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    /**
     * 获取系统配置
     * @param $name
     * @return mixed|\think\db\Query
     */
    public static function getByName($name)
    {
        if (Cache::has('configGetByName_'. $name)) {
            $config = Cache::get('configGetByName_'. $name);
        } else {
            $config = parent::__callStatic('getByName', [$name]);
            $expire = self::__callStatic('getByName', ['cache_expire'])['value'];
            Cache::set('configGetByName_'. $name, $config, $expire);
        }
        return $config;
    }

    /**
     * 判断当前是不是使用悲观锁
     * @return bool
     */
    public static function isPessimism()
    {
        return self::getByName('lock')['value'] == 'pessimism' ? true : false;
    }

    /**
     * 获取图片完整连接
     */
    public static function getImagesFullUrl($value = '')
    {
        if (stripos($value, 'http') === 0 || $value === '' || stripos($value, 'data:image') === 0) {
            return $value;
        } else {
            $upload = \think\Config::get('upload');
            if (!empty($upload['cdnurl'])) {
                return  $upload['cdnurl'] . $value;
            } else {
                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
                return $http_type . $_SERVER['HTTP_HOST'] . $value;
            }
        }
    }

    /**
     * 时间戳 - 精确到毫秒
     * @return float
     */
    public static function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
}
