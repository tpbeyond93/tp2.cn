<?php
/**
 * Created by PhpStorm.
 * User: zhengmingwei
 * Date: 2020/1/6
 * Time: 9:45 下午
 */


namespace addons\unidrink\extend;


class Snowflake extends \Godruoyi\Snowflake\Snowflake
{
    /**
     * Snowflake constructor.
     * @param int $datacenter 数据中心id
     * @param int $workerid 机器id
     * @throws \Exception
     */
    public function __construct(int $datacenter = 1, int $workerid = 1)
    {
        parent::__construct($datacenter, $workerid);
        self::setStartTimeStamp(strtotime('2020-01-01')*1000);
    }
}
