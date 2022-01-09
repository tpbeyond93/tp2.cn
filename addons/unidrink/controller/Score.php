<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/7/11
 * Time: 5:44 PM
 */


namespace addons\unidrink\controller;

use app\common\model\ScoreLog;
use fast\Date;
use think\Db;
use think\Exception;
use think\exception\PDOException;

/**
 * 积分接口
 */
class Score extends Base
{
    protected $config;

    public function _initialize()
    {
        $config = get_addon_config('signin');
        if (empty($config)) {
            $this->error('请先安装"会员签到插件"');
        }
        $this->config = $config;
        parent::_initialize();
    }

    /**
     * @ApiTitle    (签到的首页)
     * @ApiSummary  (签到的首页)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiReturn   ({"code":1,"msg":"","data":{}})
     *
     * @ApiReturnParams  (name="list", type="array", description="已签到的日期")
     * @ApiReturnParams  (name="successions", type="integer", description="连续签到次数")
     * @ApiReturnParams  (name="fillupscore", type="integer", description="补签消耗积分")
     * @ApiReturnParams  (name="isfillup", type="integer", description="是否开启补签")
     * @ApiReturnParams  (name="score", type="integer", description="今日签到将获得的积分")
     * @ApiReturnParams  (name="signin", type="integer", description="是否已签到:0=否,1=是")
     * @ApiReturnParams  (name="signinscore", type="array", description="最近7天签到获得的积分数组")
     *
     */
    public function index()
    {
        $config = $this->config;
        $signdata = $config['signinscore'];

        $lastdata = \addons\signin\model\Signin::where('user_id', $this->auth->id)->order('createtime', 'desc')->find();
        $successions = $lastdata && $lastdata['createtime'] > Date::unixtime('day', -1) ? $lastdata['successions'] : 0;

        $date = $this->request->request('date');
        $list = [];
        if ($date) {
            $date = trim($date);
            $time = strtotime($date);
            $list = \addons\signin\model\Signin::where('user_id', $this->auth->id)
                ->field('id,createtime')
                ->whereTime('createtime', 'between', [date("Y-m-1", $time), date("Y-m-1", strtotime("+1 month", $time))])
                ->select();

            foreach ($list as $key => $value) {
                $list[$key]['date'] = date('Y-m-d', $value['createtime']);
            }
        }
        $data['list'] = $list; // 已签到的日期

        $data['successions'] = $successions;
        $successions++;
        $score = isset($signdata['s' . $successions]) ? $signdata['s' . $successions] : $signdata['sn'];

        $data['fillupscore'] = $config['fillupscore']; // 补签消耗积分
        $data['isfillup'] = $config['isfillup']; // 是否开启补签
        $data['score'] = $score;

        $signinscore = [];
        foreach ($config['signinscore'] as $key => &$value) {
            $signinscore[] = [
                'title' => mb_substr($key, 1, 1) . '天',
                'desc' => '+' . $value,
                'value' => $value
            ];
        }
        $signin = \addons\signin\model\Signin::where('user_id', $this->auth->id)->whereTime('createtime', 'today')->find();
        $data['signin'] = $signin ? 1 : 0; // 是否已签到
        $data['signinscore'] = $signinscore;
        $this->success('', $data);
    }

    /**
     * @ApiTitle    (立即签到)
     * @ApiSummary  (立即签到)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiReturn   ({"code":1,"msg":"","data":{}})
     *
     * @ApiReturnParams  (name="msg", type="string", description="签到信息")
     * @ApiReturnParams  (name="date", type="string", description="签到日期")

     */
    public function dosign()
    {
        if ($this->request->isPost()) {
            $config = $this->config;
            $signdata = $config['signinscore'];

            $lastdata = \addons\signin\model\Signin::where('user_id', $this->auth->id)->order('createtime', 'desc')->find();
            $successions = $lastdata && $lastdata['createtime'] > Date::unixtime('day', -1) ? $lastdata['successions'] : 0;
            $signin = \addons\signin\model\Signin::where('user_id', $this->auth->id)->whereTime('createtime', 'today')->find();
            if ($signin) {
                $this->error('今天已签到,请明天再来!');
            } else {
                $successions++;
                $score = isset($signdata['s' . $successions]) ? $signdata['s' . $successions] : $signdata['sn'];
                Db::startTrans();
                try {
                    \addons\signin\model\Signin::create(['user_id' => $this->auth->id, 'successions' => $successions, 'createtime' => time()]);
                    \app\common\model\User::score($score, $this->auth->id, "连续签到{$successions}天");
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error('签到失败,请稍后重试');
                }
                $data['msg'] = '签到成功!连续签到' . $successions . '天!获得' . $score . '积分';
                $data['date'] = date('Y-m-d', time());
                $this->success('', $data);
            }
        }
        $this->error("请求错误");
    }


    /**
     * 签到补签
     * @ApiInternal
     */
    public function fillup()
    {
        $date = $this->request->request('date');
        $time = strtotime($date);
        $config = $this->config;
        if (!$config['isfillup']) {
            $this->error('暂未开启签到补签');
        }
        if ($time > time()) {
            $this->error('无法补签未来的日期');
        }
        if ($config['fillupscore'] > $this->auth->score) {
            $this->error('你当前积分不足');
        }
        $days = Date::span(time(), $time, 'days');
        if ($config['fillupdays'] < $days) {
            $this->error("只允许补签{$config['fillupdays']}天的签到");
        }
        $count = \addons\signin\model\Signin::where('user_id', $this->auth->id)
            ->where('type', 'fillup')
            ->whereTime('createtime', 'between', [Date::unixtime('month'), Date::unixtime('month', 0, 'end')])
            ->count();
        if ($config['fillupnumsinmonth'] <= $count) {
            $this->error("每月只允许补签{$config['fillupnumsinmonth']}次");
        }
        Db::name('signin')->whereTime('createtime', 'd')->select();
        $signin = \addons\signin\model\Signin::where('user_id', $this->auth->id)
            ->where('type', 'fillup')
            ->whereTime('createtime', 'between', [$date, date("Y-m-d 23:59:59", $time)])
            ->count();
        if ($signin) {
            $this->error("该日期无需补签到");
        }
        $successions = 1;
        $prev = $signin = \addons\signin\model\Signin::where('user_id', $this->auth->id)
            ->whereTime('createtime', 'between', [date("Y-m-d", strtotime("-1 day", $time)), date("Y-m-d 23:59:59", strtotime("-1 day", $time))])
            ->find();
        if ($prev) {
            $successions = $prev['successions'] + 1;
        }
        Db::startTrans();
        try {
            \app\common\model\User::score(-$config['fillupscore'], $this->auth->id, '签到补签');
            //寻找日期之后的
            $nextList = \addons\signin\model\Signin::where('user_id', $this->auth->id)
                ->where('createtime', '>=', strtotime("+1 day", $time))
                ->order('createtime', 'asc')
                ->select();
            foreach ($nextList as $index => $item) {
                //如果是阶段数据，则中止
                if ($index > 0 && $item->successions == 1) {
                    break;
                }
                $day = $index + 1;
                if (date("Y-m-d", $item->createtime) == date("Y-m-d", strtotime("+{$day} day", $time))) {
                    $item->successions = $successions + $day;
                    $item->save();
                }
            }
            \addons\signin\model\Signin::create(['user_id' => $this->auth->id, 'type' => 'fillup', 'successions' => $successions, 'createtime' => $time + 43200]);
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error('补签失败,请稍后重试');
        } catch (Exception $e) {
            Db::rollback();
            $this->error('补签失败,请稍后重试');
        }

        $this->success('补签成功');
    }

    /**
     * 排行榜
     * @ApiInternal
     */
    public function rank()
    {
        $data = \addons\signin\model\Signin::with(["user"])
            ->where("createtime", ">", Date::unixtime('day', -1))
            ->field("user_id,MAX(successions) AS days")
            ->group("user_id")
            ->order("days", "desc")
            ->limit(10)
            ->select();
        foreach ($data as $index => $datum) {
            $datum->getRelation('user')->visible(['id', 'username', 'nickname', 'avatar']);
        }
        $this->success("", "", ['ranklist' => collection($data)->toArray()]);
    }


    /**
     * @ApiTitle    (积分记录)
     * @ApiSummary  (积分记录)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=platform, type=string, required=false, description="平台")
     * @ApiHeaders  (name=token, type=string, required=true, description="登录token")
     * @ApiParams   (name="page", type="integer", required=false, description="页面")
     * @ApiParams   (name="pagesize", type="integer", required=false, description="页数")
     * @ApiReturn   ({"code":1,"msg":"","data":[]})
     *
     * @ApiReturnParams  (name="id", type="integer", description="积分记录id")
     * @ApiReturnParams  (name="user_id", type="integer", description="用户id")
     * @ApiReturnParams  (name="score", type="integer", description="获得积分")
     * @ApiReturnParams  (name="before", type="integer", description="获得积分前积分")
     * @ApiReturnParams  (name="after", type="integer", description="获得积分后积分")
     * @ApiReturnParams  (name="memo", type="integer", description="签到记录提示")
     * @ApiReturnParams  (name="createtime", type="integer", description="签到时间")
     */
    public function log()
    {
        $page = $this->request->post('page', 1);
        $pageSize = $this->request->post('pagesize', 20);

        $log = new ScoreLog();
        $list = $log
            ->where(['user_id' => $this->auth->id])
            ->order('createtime desc')
            ->page($page, $pageSize)
            ->select();

        $this->success('', $list);
    }
}
