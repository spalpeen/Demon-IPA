<?php
use \Cron\CronExpression;
class Task_model extends CI_Model
{
    /**
     * 待执行任务列表
     */
    private static $taskTable;

    /**
     * swoole_table大小
     */
    const TASK_SIZE = 1024;

    private static $columns = array(
        'id' => array(\Swoole_table::TYPE_INT, 4),
        'taskName' => array(\Swoole_table::TYPE_STRING, 256),
        'rule' => array(\Swoole_table::TYPE_STRING, 256),
        'command' => array(\Swoole_table::TYPE_STRING, 256),
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->database(ENVIRONMENT);
        $this->load->library('dist_lock');
    }

    /**
     * 初始化swoole_table
     */
    public function initTable()
    {
        self::$taskTable = new \Swoole_table(self::TASK_SIZE * 2);
        foreach(self::$columns as $key => $v) {
            self::$taskTable->column($key, $v[0], $v[1]);
        }
        self::$taskTable->create();
    }

    /**
     * 在数据库拉取当前时间可执行的任务（最小精度为分钟）；并添加到执行队列中
     * @throws Exception
     */
    public function initTask()
    {
        $db = $this->db;
        $query = $db->select('*')->get('t_crontab');
        $crons = $query->result_array();
        $now = new DateTimeImmutable("now");
        //在mysql中获取所有的任务，循环判断加锁，获得锁才放到任务队列中
        foreach($crons as $cron) {
            $rule = $cron['cron_rule'];
            $isDue = CronExpression::factory((string)$rule)->isDue($now);
            if (!$isDue) {
                continue;
            }
            $lock = $this->dist_lock->lock($cron['id'], 10000);
            if ($lock !== true) {
                continue;
            }
            self::$taskTable->set($cron['id'], [
                'id' => $cron['id'],
                'taskName' => $cron['cron_name'],
                'rule' => $cron['cron_rule'],
                'command' => $cron['cron_execution'],
            ]);
            $this->execTask($cron);
        }
    }

    /**
     * 开启协程执行任务；记录执行日志
     * @param $task
     */
    public function execTask($task)
    {
        $startTime = intval(microtime(true) * 1000);
        go(function($com, $startTime) {
            $ret = co::exec($com['cron_execution']);
            echo 'exec finished' . PHP_EOL;
            $ret['startTime'] = $startTime;
            $ret['endTime'] = intval(microtime(true) * 1000);
            self::logTaskResult($com, $ret);
            self::$taskTable->del($com['id']);
        }, $task, $startTime);
    }

    /**
     * 刷新锁时间
     */
    public function refreshTtl()
    {
        //刷新任务表中的锁时间
        foreach(self::$taskTable as $key => $item) {
            go(function($cronId) {
                $this->dist_lock->expire($cronId, 10);
            }, $key);
        }
    }

    /**
     * 记录任务执行日志
     * @param $command
     * @param $result
     */
    public function logTaskResult($command, $result)
    {
        $log = [
            'cron_id' => $command['id'],
            'start_time' => $result['startTime'],
            'end_time' => $result['endTime'],
            'exec_ip' => '127.0.0.1',
            'exec_status' => $result['code'],
            'exec_result' => $result['output']
        ];

        $this->db->insert('t_crontab_log', $log);
    }
}