<?php
class Server
{
    public $serv;
    const HOST = '0.0.0.0';
    const PORT = 9501;
    const WORKER_NUM = 2;
    const TASK_NUM = 2;

    public $config = array(
        'worker_num' => 2,
        'task_worker_num' => 2,
    );
    
    public function __construct()
    {
        $serv = new \Swoole\Server(self::HOST, self::PORT, SWOOLE_BASE);
        $serv->set($this->config);
        $serv->on('Connect', array($this, 'onConnect'));
        $serv->on('Receive', array($this, 'onReceive'));
        $serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $serv->on('PipeMessage', array($this, 'onPipeMessage'));
        $serv->on('Task', array($this, 'onTask'));
        $serv->on('Finish', array($this, 'onFinish'));
        $serv->on('Close', array($this, 'onClose'));
        $this->serv = $serv;
    }

    public function onConnect(swoole_server $server, int $fd, int $reactorId)
    {
        echo 'on connect. fd is ' . $fd . ' and reactor id is ' . $reactorId . PHP_EOL;
    }

    public function onReceive(swoole_server $server, int $fd, int $reactorId, string $data)
    {
        echo 'on receive. fd is ' . $fd . ' and reactor id is ' . $reactorId . '. data is ' . $data . PHP_EOL;
    }

    public function onWorkerStart(swoole_server $server, int $workerId)
    {
        if ($server->taskworker) {
            if ($workerId == self::WORKER_NUM) {
                \Swoole\Timer::tick(1000, function($timerId) use ($server) {
                    $server->sendMessage('initTask', self::WORKER_NUM - 1);
                });
            }
            echo 'on worker start. task worker id is ' . $workerId . PHP_EOL;
            if ($workerId == self::WORKER_NUM + 1) {
                \Swoole\Timer::tick(1000, function($timerId) use ($server) {
                    $server->sendMessage('execTask', self::WORKER_NUM - 1);
                });
            }
        } else {
            go(function() {
                $mysql = new Swoole\Coroutine\MySQL();
                $mysql->connect([
                    'host' => '127.0.0.1',
                    'port' => 3306,
                    'user' => 'root',
                    'password' => '123456',
                    'database' => 'db_hbg_php_dts',
                ]);
            });
            echo 'on worker start. worker id is ' . $workerId . PHP_EOL;
        }
    }

    public function onPipeMessage(swoole_server $server, int $srcWorkerId, $message)
    {
        switch($message) {
            case 'execTask' : 
                Task::getAndExecTask();
                break;
            case 'initTask': 
                Task::initTask();
                break;
        }
        
        echo 'on pipe message. src worker id is ' . $srcWorkerId . '. receive message' . json_encode($message) . PHP_EOL;
    }

    public function onTask(swoole_server $server, int $taskId, int $srcWorkerId, $data)
    {
        sleep(2);
        echo date('Y-m-d H:i:s') . ' on task. task id is ' . $taskId . '. src worker id is ' . $srcWorkerId . '. task data is ' . json_encode($data) . PHP_EOL;
        return ' after 2s task finish~';
    }

    public function onFinish(swoole_server $server, int $taskId, string $data)
    {
        echo date('Y-m-d H:i:s') . ' on finish. task id is ' . $taskId . '. data is ' . $data . PHP_EOL;
    }

    public function onClose(swoole_server $server, int $fd, int $reactorId)
    {
        echo 'on close. fd is ' . $fd . ' and reactor id is ' . $reactorId . PHP_EOL;
    }

    public function start()
    {
        $this->serv->start();
    }
}

class Task
{
    /**
     * 待执行任务列表
     */
    private static $taskTable;

    /**
     * 执行任务列表
     */
    private static $execTable;

    private static $mysql;

    const TASK_SIZE = 1024;

    private static $execColumns = array(
        'taskName' => array(\Swoole_table::TYPE_STRING, 256),
        'status' => array(\Swoole_table::TYPE_INT, 1),
        'startTime' => array(\Swoole_table::TYPE_INT, 4),
        'endTime' => array(\Swoole_table::TYPE_INT, 4),
    );

    private static $columns = array(
        'id' => array(\Swoole_table::TYPE_INT, 4),
        'taskName' => array(\Swoole_table::TYPE_STRING, 256),
        'rule' => array(\Swoole_table::TYPE_STRING, 256),
        'command' => array(\Swoole_table::TYPE_STRING, 256),
    );

    public static function initTable()
    {
        self::$taskTable = new \Swoole_table(self::TASK_SIZE * 2);
        self::$execTable = new \Swoole_table(self::TASK_SIZE * 2);
        foreach(self::$columns as $key => $v) {
            self::$taskTable->column($key, $v[0], $v[1]);
        }
        foreach(self::$execColumns as $key => $v) {
            self::$execTable->column($key, $v[0], $v[1]);
        }
        self::$taskTable->create();
        self::$execTable->create();
    }

    public static function initTask()
    {
        DB::initDB();
        $crons = DB::query('select * from t_crontab');
        //在mysql中获取所有的任务，循环判断加锁，获得锁才放到任务队列中
        foreach($crons as $cron) {
            self::$taskTable->set($cron['id'], [
                'id' => $cron['id'],
                'taskName' => $cron['cron_name'],
                'rule' => $cron['cron_rule'],
                'command' => $cron['cron_execution'],
            ]);
        }

        echo 'init task' . PHP_EOL;
    }

    public static function getTasks()
    {
        return self::$taskTable;
    }

    public static function getAndExecTask()
    {
        foreach(self::$taskTable as $key => $item) {
            $startTime = intval(microtime(true) * 1000);
            self::$taskTable->del($key);
            go(function($com, $startTime) {
                $ret = co::exec($com['command']);
                print_r($ret);
                $ret['startTime'] = $startTime;
                $ret['endTime'] = intval(microtime(true) * 1000);
                self::logTaskResult($com, $ret);
            }, $item, $startTime);
        }
    }

    public static function _initMysql()
    {
        if (!is_null(self::$mysql)) {
            return;
        }
        self::$mysql = new Swoole\Coroutine\MySQL();
        self::$mysql->connect([
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => '123456',
            'database' => 'db_hbg_php_dts'
        ]);
        $table = self::$mysql->query('select * from t_crontab');
        echo json_encode($table);
    }

    public static function logTaskResult($command, $result)
    {
        $log = [
            'cron_id' => $command['id'],
            'start_time' => $result['startTime'],
            'end_time' => $result['endTime'],
            'exec_ip' => '127.0.0.1',
            'exec_status' => $result['code'],
            'exec_result' => $result['output']
        ];

        DB::insert('t_crontab_log', $log);
    }
}

class DB
{
    private static $mysql = null;

    public static function initDB()
    {
        if (!is_null(self::$mysql)) {
            return;
        }
        self::$mysql = new Swoole\Coroutine\MySQL();
        self::$mysql->connect([
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => '123456',
            'database' => 'db_hbg_php_dts'
        ]);
    }

    public static function query($sql)
    {
        return self::$mysql->query($sql);
    }

    public static function insert($table, $value)
    {
        // $sql = 'INSERT INTO ' . $table . ' (`' . implode('`,`', array_keys($value)) . '`) VALUES ('
        //     . implode(',', array_pad(array(), count($value), '?')) . ')';
        $sql = 'INSERT INTO ' . $table . ' (`' . implode('`,`', array_keys($value)) . '`) VALUES ("'
            . implode('","', array_values($value)) . '")';
        
        // $stmt = self::$mysql->prepare($sql);
        // if ($stmt === false) {
        //     return false;
        // }
        echo $sql . PHP_EOL;
        // $ret = $stmt->execute($value);
        self::$mysql->query($sql);
        var_export(self::$mysql->error . PHP_EOL);
    }
}

Task::initTable();
$server = new Server();
$server->start();
