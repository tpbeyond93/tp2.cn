<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2019/10/22
 * Time: 9:34 下午
 */

namespace addons\unidrink\controller;

use app\common\controller\Api;
use app\common\library\Auth;
use think\Cache;
use think\Lang;

/**
 * 基础类
 * @ApiInternal
 */
class Base extends Api
{
    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = ['*'];

    /**
     * 允许频繁访问的接口（方法格式：小写）
     * @var array
     */
    protected $frequently = [];

    public function _initialize()
    {
        parent::_initialize();

        $this->loadUniDrinkLang();

        $this->limitVisit();
    }

    /**
     * 限制接口访问频率
     * @param int $millisecond
     */
    public function limitVisit($millisecond = 200) {
        $millisecond = $this->request->request('millisecond', $millisecond);

        // 限制200毫秒 防止1秒两刀 （双击甚至三击，同一时间导致接口请求两次以上）
        $action = $this->request->action();
        if (!in_array($action, $this->frequently) && $this->auth && $this->auth->isLogin() && $millisecond > 0) {
            $controller = $this->request->controller();
            if (Cache::has($controller.'_'.$action.'_'.$this->auth->id)) {
                if (Cache::get($controller.'_'.$action.'_'.$this->auth->id) + $millisecond > \addons\unidrink\model\Config::getMillisecond()) {
                    $this->error(__('Frequent interface requests'));
                }
            }
            Cache::set($controller.'_'.$action.'_'.$this->auth->id, \addons\unidrink\model\Config::getMillisecond(), 1);
        }
    }

    /**
     * 加载语言文件
     */
    protected function loadUniDrinkLang()
    {
        $route = $this->request->route();
        $lang = $this->request->header('lang') ?: 'zh-cn';
        $path = ADDON_PATH . $route['addon'] . '/lang/' . $lang . '/' . str_replace('.', '/', $route['controller']) . '.php';
        Lang::load(ADDON_PATH . $route['addon'] . '/lang/'.$lang.'.php'); // 默认语言包
        Lang::load($path);
    }

}
