<?php

namespace BChat;

use Knp\Provider\ConsoleServiceProvider;

class Application extends \Silex\Application {

	public function __construct($debug = false)
	{
		parent::__construct();

		$this['debug'] = $debug;

		$this->registerProviders();
	}

	protected function registerProviders()
	{
		$this->register(new ConsoleServiceProvider(), [
			'console.name' => 'bchat';
        	'console.version' => '0.0.1';
		]);
	}
	
}