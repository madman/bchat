<?php

namespace BChat\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Predis\CommunicationException;

class Consumer extends Command {

    protected function configure()
    {
        $this
	        ->setName('chat:run')
	        ->setDescription("Run chat's consumer")
	    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        sleep(5);

        while(true) {
        	$output->writeln('Start listen: ' . $app['name']);

        	$cb = function($message) use ($output, $app) {
                try {
                    $app['predis']->lpush('chat', $message->body);
                    $output->writeln('Proccesed: ' . $message->body);

                    return true;
                } catch (CommunicationException $e) {
                    $output->writeln('Failed: ' . $message->body);
                    return false;
                }
        	};

        	$app['amqp']->consume($app['name'], $cb);
        }	
    }	
}
