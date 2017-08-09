<?php

if ('cli-server' === php_sapi_name()) {
    if (is_file(__DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']))) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

$app = new BChat\Application(false);

$app->get('/', function () use ($app) {
  return 'Hello, World!';
});

$app->run();