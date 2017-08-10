<?php

namespace BChat;

use Knp\Provider\ConsoleServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application extends \Silex\Application {

    public function __construct($debug = false)
    {
        parent::__construct();

        $this['debug'] = $debug;

        $this->registerProviders();
        $this->registerControllers();
    }

    protected function registerProviders()
    {
        $this->register(new ConsoleServiceProvider(), [
            'console.name' => 'bchat',
            'console.version' => '0.0.1',
        ]);

        $this->register(new \Silex\Provider\TwigServiceProvider(), [
            'twig.path' => __DIR__.'/../app/Resources/views',
        ]);

        $this->register(new \EXS\RabbitmqProvider\Providers\Services\RabbitmqProvider(), [
            'rabbit.connections' => [
                'default' => [
                    'host' => 'rabbit',
                    'port' => 5672,
                    'user' => $_ENV["RABBIT_ENV_RABBITMQ_DEFAULT_USER"],
                    'password' => $_ENV["RABBIT_ENV_RABBITMQ_DEFAULT_PASS"],
                    'vhost' => $_ENV["RABBIT_ENV_RABBITMQ_DEFAULT_VHOST"],
                ]
            ],
            'exs.rabbitmq.env' => [
                'exchange' => 'messages',
                'type' => 'direct',
                'queue' => 'messages',
                'key' => 'messages',
            ]
        ]);
    }

    protected function registerControllers()
    {
        $app = $this;

        $this->get('/', function () use ($app) {
            return $app['twig']->render('base.html.twig');
        });

        $this->post('/say', function (Request $request) use ($app) {
            $message = $request->get('message');

            return $app->json(['message' => $message]);
        });
    }
    
}