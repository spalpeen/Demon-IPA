<?php
class Tools extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
        echo 'construct' . PHP_EOL;
        $this->load->library('dist_lock');
    }

    public function message($to = 'World')
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1');
        $ret = $redis->set('name', 'value', ['nx', 'ex' => 100]);
        var_export($ret);echo PHP_EOL;
        $ret = $redis->set('name', 'value', ['nx', 'ex' => 100]);
        var_export($ret);echo PHP_EOL;

        $ret = $redis->get('name');
        echo $ret . PHP_EOL;
        echo "Hello {$to}!".PHP_EOL;
    }
}