<?php

//error_reporting(E_ALL);

include \dirname(__DIR__) . '/Parith/App.php';

$config = include __DIR__ . '/Config/ExampleApp.php';

$app = new \Parith\App($config);

$app->run();