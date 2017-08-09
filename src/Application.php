<?php

namespace BChat;

use Knp\Provider\ConsoleServiceProvider;

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
	}

	protected function registerControllers()
	{
		$app = $this;

		$this->get('/', function () use ($app) {
  			return $app['twig']->render('base.html.twig');
		});
	}
	
}