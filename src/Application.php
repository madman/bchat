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

        $this->register(new \Predis\Silex\ClientServiceProvider(), [
            'predis.parameters' => 'tcp://127.0.0.1:6379',
            'predis.options'    => [
                'profile' => '3.0',
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
            $message = trim($request->get('message'));

            if ($message) {
                $post = [
                    'message' => $message,
                    'date' => date('Y-m-d H:i:s'),
                    'author' => $app['name'],
                ];

                $msg = json_encode($post);

                $app['amqp']->publish($msg);

                return $app->json(['post' => $post, 'msg' => $msg]);
            }

            return $app->json(['error' => 'No message!'], 500);
        });

        $this->get('/last/{num}', function($num) use ($app) {

            $num = (int)$num;
            $num = ($num > 0) ? $num : 10;


            $posts = $app['predis']->lrange('chat', 0, $num);

            foreach ($posts as &$post) {
                $post  = json_decode($post);
            }
            $all = $app['predis']->llen('chat');

            return $app->json(['posts' =>  $posts, 'all' => $all]);
        });
    }

    protected function registerCommands()
    {
        $this['console']->add(new \BChat\Command\Consumer());
    }
    
}
