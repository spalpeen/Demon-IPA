<?php
class JobServer extends CI_Controller
{
    public $serv;
    const HOST = '0.0.0.0';
    const PORT = 9501;
    const WORKER_NUM = 2;
    const TASK_NUM = 2;

    static $sConfig = array(
        'worker_num' => 2,
        'task_worker_num' => 2,
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->model('task_model');
        $serv = new \Swoole\Server(self::HOST, self::PORT, SWOOLE_BASE);
        $serv->set(self::$sConfig);
        $serv->on('Receive', array($this, 'onReceive'));
        $serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $serv->on('PipeMessage', array($this, 'onPipeMessage'));
        $serv->on('Task', array($this, 'onTask'));
        $serv->on('Finish', array($this, 'onFinish'));
        $serv->on('Close', array($this, 'onClose'));
        $this->serv = $serv;
        $this->task_model->initTable();
    }

    public function onReceive(swoole_server $server, int $fd, int $reactorId, string $data)
    {
        echo 'on receive. fd is ' . $fd . ' and reactor id is ' . $reactorId . '. data is ' . $data . PHP_EOL;
    }

    /**
     * worker进程或者task进程启动时执行的脚本
     * @param swoole_server $server
     * @param int $workerId
     */
    public function onWorkerStart(swoole_server $server, int $workerId)
    {
        if ($server->taskworker) {
            if ($workerId == self::WORKER_NUM) {
                //整点进入定时器
                \Swoole\Timer::after((60 - date('s')) * 1000, function() use ($server) {
                    \Swoole\Timer::tick(60000, function($timerId) use ($server) {
                         $server->sendMessage('execTask', self::WORKER_NUM - 1);
                    });
                });
            } else if ($workerId == self::WORKER_NUM + 1) {
                \Swoole\Timer::tick(1000, function($timerId) use ($server) {
                    $server->sendMessage('refreshTtl', self::WORKER_NUM - 1);
                });
            }
        }
    }

    /**
     * 接收sendMessage管道消息触发的回调
     * @param swoole_server $server
     * @param int $srcWorkerId
     * @param $message
     */
    public function onPipeMessage(swoole_server $server, int $srcWorkerId, $message)
    {
        switch($message) {
            //刷新锁过期时间
            case 'refreshTtl' :
                $this->task_model->refreshTtl();
                break;
            case 'execTask':
                $this->task_model->initTask();
                break;
        }
        
        echo 'on pipe message. src worker id is ' . $srcWorkerId . '. receive message' . json_encode($message) . PHP_EOL;
    }

    /**
     * worker进程投递任务后执行的回调函数
     * @param swoole_server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @param $data
     * @return string
     */
    public function onTask(swoole_server $server, int $taskId, int $srcWorkerId, $data)
    {
        sleep(2);
        echo date('Y-m-d H:i:s') . ' on task. task id is ' . $taskId . '. src worker id is ' . $srcWorkerId . '. task data is ' . json_encode($data) . PHP_EOL;
        return ' after 2s task finish~';
    }

    /**
     * worker进程投递的任务执行完成后回调；task中必须有return才会触发
     * @param swoole_server $server
     * @param int $taskId
     * @param string $data
     */
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