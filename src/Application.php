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
        $this['name'] = getenv('QUEUE');

        $this->registerProviders();
        $this->registerControllers();
        $this->registerCommands();
    }

    protected function registerProviders()
    {
        $this->register(new ConsoleServiceProvider(), [
            'console.name' => $this['name'],
            'console.version' => '0.0.1',
        ]);

        $this->register(new \Silex\Provider\TwigServiceProvider(), [
            'twig.path' => __DIR__.'/../app/Resources/views',
        ]);

        $this->register(new Provider\AmqpProvider(), [
            'amqp.connection' => [
                'host' => 'rabbit',
                'port' => 5672,
                'user' => getenv("RABBIT_ENV_RABBITMQ_DEFAULT_USER"),
                'password' => getenv("RABBIT_ENV_RABBITMQ_DEFAULT_PASS"),
                'vhost' => getenv("RABBIT_ENV_RABBITMQ_DEFAULT_VHOST"),

            ],
        ]);
    }

    protected function registerControllers()
    {
        $app = $this;

        $this->get('/', function () use ($app) {
            return $app['twig']->render('base.html.twig');
        });

        $this->post('/say', function (Request $request) use ($app) {
            $message = trim($request->get('message'));

            if ($message) {

                $app['amqp']->publish($message);

                return $app->json(['message' => $message]);
            }

            return $app->json(['error' => 'No message!'], 500);
        });
    }

    protected function registerCommands()
    {
        $this['console']->add(new \BChat\Command\Consumer());
    }
    
}