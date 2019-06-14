<?php
/**
 * Created by PhpStorm.
 * User: suhui02
 * Date: 2019/6/13
 * Time: 19:24
 */
return $config = array(
    'mysql' => array(
        'host' => '127.0.0.1',
        'port' => '',
        'username' => 'root',
        'password' => '123456',
        'database' => 'test',
    ),

    'redis' => array(
        'host' => '127.0.0.1',
        'port' => 6739,
        'auth' => '123456',
        'timeout' => 0,
        'socket' => '/tmp/redis.sock',
        'socketType' => 'tcp',
    ),
);