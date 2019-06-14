<?php

$config['socket_type'] = env('redis.socketType'); //`tcp` or `unix`
$config['socket'] = env('redis.socket'); // in case of `unix` socket type
$config['host'] = env('redis.host');
$config['password'] = env('redis.auth');
$config['port'] = env('redis.port');
$config['timeout'] = env('redis.timeout');