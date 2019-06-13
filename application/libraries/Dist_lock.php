<?php
/**
 * 分布式锁
 * User: committer
 * Date: 2019/6/3
 * Time: 17:22
 */

class Dist_lock
{

    private $_server_config = [];

    private $_redis_default_server_config = [
        'socket_type' => 'tcp',
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => 6379,
        'timeout' => 0
    ];

    private $lock_key_prefix = 'lock:';

    private $clockDriftFactor = 0.01;

    private $redis_instances;

    function __construct()
    {
        $CI =& get_instance();
        if ($CI->config->load('redis', TRUE, TRUE)) {
            $this->_server_config = $CI->config->item('redis');
        } else {
            $this->_server_config = $this->_redis_default_server_config;
        }
    }

    public function lock($key, $ttl)
    {
        $this->_initRedis();
        $lock_key = $this->lock_key_prefix . $key;
        $token = uniqid();

        $startTime = microtime(true) * 1000;

        $get_lock = $this->redis_instances->set($lock_key, $token, ['NX', 'PX' => $ttl]);
        if ($get_lock) {
            return true;
        }

        //漂移
        $drift = ($ttl * $this->clockDriftFactor) + 2;
        //有效时间
        $validityTime = $ttl - (microtime(true) * 1000 - $startTime) - $drift;
        if ($validityTime > 0) {
            return [
                'status' => true,
                'validity' => $validityTime,
                'key' => $key,
                'token' => $token,
            ];
        } else {
            //有效时间过期 释放锁
            $this->unlock_instance($key, $token);
            return [
                'status' => false,
                'validity' => 0,
                'key' => '',
                'token' => '',
            ];
        }
    }

    public function unlock(array $lock)
    {
        $this->_initRedis();
        $lock_key = $this->lock_key_prefix . $lock['key'];
        $token = $lock['token'];
        $this->unlock_instance($lock_key, $token);
    }

    public function expire($key, $ttl)
    {
        $redisKey = $this->getRedisKey($key);
        return $this->redis_instances->expire($redisKey, $ttl);
    }

    public function get($key)
    {
        $redisKey = $this->getRedisKey($key);
        return $this->redis_instances->get($redisKey);
    }

    private function _initRedis()
    {
        if (empty($this->instances)) {
            $config = $this->_server_config;
            $host = $config['host'];
            $port = $config['port'];
            $timeout = $config['timeout'];
            // list($host, $port, $timeout) = $this->_server_config;
            $redis = new \Redis();
            $redis->connect($host, $port, $timeout);
            if (isset($config['password'])) {
                $redis->auth($config['password']);
            }
            $this->redis_instances = $redis;
        }
    }

    private function unlock_instance($key, $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        return $this->redis_instances->eval($script, [$key, $token], 1);
    }

    private function getRedisKey($key)
    {
        return $this->lock_key_prefix . $key;
    }
}
