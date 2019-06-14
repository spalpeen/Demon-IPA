<?php

/**
 * User: kongming01
 * Date: 2019/5/27
 * Time: 15:39
 */
class Etcd
{

    private static $instance;

    private $host;
    private $port;

    public function __construct($host = '127.0.0.1', $port = '2379')
    {
        $this->host = $host;
        $this->port = $port;

        if (is_null(self::$instance)) {
            self::$instance = new \Etcd\Client($host . ':' . $port);;
        }

        return self::$instance;
    }

    public function set($key, $value)
    {
        return self::$instance->put($key, $value);
    }

    public function get($key)
    {
        return self::$instance->get($key);
    }

    public function get_all()
    {
        return self::$instance->getAllKeys();
    }

    public function del($key)
    {
        return self::$instance->del($key);
    }

    public function get_by_prefix($prefix)
    {
        return self::$instance->getKeysWithPrefix($prefix);
    }
}