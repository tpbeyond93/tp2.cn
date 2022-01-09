<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/6/3
 * Time: 10:46 PM
 */


namespace addons\unidrink\controller;

use addons\unidrink\model\Service;

/**
 * 个人中心
 */
class Mine extends Base
{
    protected $noNeedLogin = ['service'];

    /**
     * @ApiTitle    (我的服务)
     * @ApiSummary  (获取我的服务)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiParams   (name="pid", type=integer, required=false, description="服务id，默认为0")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     * @ApiReturnParams  (name="id", type="integer", description="服务id")
     * @ApiReturnParams  (name="type", type="string", description="服务类型")
     * @ApiReturnParams  (name="image", type="string", description="服务icon")
     * @ApiReturnParams  (name="app_id", type="string", description="要跳转的小程序")
     * @ApiReturnParams  (name="pages", type="string", description="要跳转的小程序页面")
     *
     */
    public function service()
    {
        $pid = $this->request->request('pid', 0);
        $servece = new Service();
        $list = $servece
            ->where(['pid' => $pid, 'status' => Service::STATUS_ON])
            ->field('id,type,name,image,app_id,pages')
            ->select();
        $this->success('', $list);
    }

    /**
     * @ApiTitle    (我的服务详情)
     * @ApiSummary  (获取我的服务详情)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=cookie, type=string, required=false, description="用户会话的cookie")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="id", type=integer, required=true, description="服务id")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     * @ApiReturnParams  (name="id", type="integer", description="服务id")
     * @ApiReturnParams  (name="type", type="string", description="服务类型")
     * @ApiReturnParams  (name="image", type="string", description="服务icon")
     * @ApiReturnParams  (name="app_id", type="string", description="要跳转的小程序")
     * @ApiReturnParams  (name="pages", type="string", description="要跳转的小程序页面")
     * @ApiReturnParams  (name="content", type="string", description="富文本内容")
     */
    public function serviceContent()
    {
        $id = $this->request->request('id');
        $content = (new Service)
            ->where(['id' => $id, 'status' => Service::STATUS_ON])
            ->find();
        $this->success('', $content);
    }
}
