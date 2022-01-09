<?php
namespace addons\unidrink;

use app\common\library\Menu;
use think\Addons;
use think\Loader;

/**
 * 插件
 */
class Unidrink extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu=[];
        $config_file= ADDON_PATH ."unidrink" . DS.'config'.DS. "menu.php";
        if (is_file($config_file)) {
            $menu = include $config_file;
        }
        if($menu){
            Menu::create($menu);
        }
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        $info=get_addon_info('unidrink');
        Menu::delete(isset($info['first_menu'])?$info['first_menu']:'unidrink');
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        $info=get_addon_info('unidrink');
        Menu::enable(isset($info['first_menu'])?$info['first_menu']:'unidrink');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        $info=get_addon_info('unidrink');
        Menu::disable(isset($info['first_menu'])?$info['first_menu']:'unidrink');
    }

    public function appInit()
    {
        Loader::addNamespace('Hashids', ADDON_PATH . 'unidrink' . DS . 'library' . DS . 'Hashids' . DS);
        Loader::addNamespace('Godruoyi\Snowflake', ADDON_PATH . 'unidrink' . DS . 'library' . DS . 'Godruoyi' . DS . 'Snowflake' . DS);
    }
}
