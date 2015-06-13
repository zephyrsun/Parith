<?php

return array(
    'namespace' => 'ExampleApp',
    'router' => array(
        'default' => array('Index', 'index'),
    ),
    'database' => array(
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'root',
        'password' => '123456',
    ),
    'memcache' => array(
        array(
            'host' => '127.0.0.1',
            'port' => 11211,
        )
    ),
);