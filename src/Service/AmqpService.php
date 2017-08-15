<?php

namespace BChat\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class AmqpService {

    const EXCHANGE = 'chat';

    const NO_ACK = true;
    const ACK = false;

    /**
     * @var AMQPStreamConnection
     */
    protected $_connection;
    protected $_channel;

    public function __construct($host, $port, $user, $password, $vhost)
    {
        $this->_connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
        $this->_channel = $this->_connection->channel();
    }

    public function publish($text)
    {
        $this->_channel->exchange_declare(static::EXCHANGE, 'fanout', false, false, false);

        $message = new AMQPMessage($text, [
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $this->_channel->basic_publish($message, static::EXCHANGE);
    }

    public function consume($queue, $callback)
    {
        $this->_channel->exchange_declare(static::EXCHANGE, 'fanout', false, false, false);
        $this->_channel->queue_declare($queue, false, false, false, false);
        $this->_channel->queue_bind($queue, static::EXCHANGE);

        /**
         * @param \PhpAmqpLib\Message\AMQPMessage $message
         */
        $cb = function($message) use ($callback) {
            $proccessed = call_user_func($callback, $message);

            if ($proccessed) {
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            }
        };

        $this->_channel->basic_consume($queue, $queue, false, self::ACK, false, false, $cb);

        while(count($this->_channel->callbacks)) {
            $this->_channel->wait();
        }

    }

    public function __destruct()
    {
        $this->_channel->close();
        $this->_connection->close();
    }

}