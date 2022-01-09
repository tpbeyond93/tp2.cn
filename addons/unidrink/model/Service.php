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

class Service extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'unidrink_service';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 类型:pages=页面,miniprogram=跳转小程序,menu=菜单,content=内容
    const TYPE_PAGES = 'pages';
    const TYPE_MINIPROGRAM = 'miniprogram';
    const TYPE_MENU = 'menu';
    const CONTENT = 'content';

    // 是否上线
    const STATUS_ON = 1; // 是
    const STATUS_OFF = 0; // 否

    /**
     * 处理图片
     * @param $value
     * @return string
     */
    public function getImageAttr($value) {
        return Config::getImagesFullUrl($value);
    }

}
