<?php

namespace BChat\Provider;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use BChat\Service\AmqpService;

class AmqpProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
    	$app['amqp'] = function ($app) {
    		return new AmqpService($app['amqp.connection']['host'], $app['amqp.connection']['port'],  $app['amqp.connection']['user'], $app['amqp.connection']['password'], $app['amqp.connection']['vhost'] );
    	};
    }
}
