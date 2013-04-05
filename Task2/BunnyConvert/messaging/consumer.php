<?php
require('../constants.php');
require('../config.php');

use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Basic consumer superclass.
 *
 * @author Markus Deutschl <deutschl.markus@gmail.com>
 */
abstract class Consumer {
  protected $connection;
  protected $channel;
  protected $queue;
  protected $consumerTag;

  /**
   * Constructor for the consumer.
   * @param string $host        The RabbitMQ host.
   * @param string $port        The RabbitMQ port.
   * @param string $user        The RabbitMQ user.
   * @param string $pass        The RabbitMQ password.
   * @param string $vhost       The RabbitMQ virtual host.
   * @param string $consumerTag The consumer tag to identify the consumer process.
   */
  public function __construct($host, $port, $user, $pass, $vhost, $queue, $consumerTag) {
    // Establish the connection to RabbitMQ.
    $this->connection = new AMQPConnection($host, $port, $user, $pass, $vhost);
    $this->channel = $this->connection->channel();
    $this->queue = $queue;
    $this->consumerTag = $consumerTag;
    // Register the shutdown() function to ensure the channel and the connection get closed.
    register_shutdown_function(array($this, 'shutdown'));
  }

  /**
   * Processes messages from the queue. All Subclasses provide their implementations.
   * @param \PhpAmqpLib\Message\AMQPMessage $msg The message to process.
   * @return null No return value.
   */
  public abstract function processMessage($msg);

  /**
   * Starts the consumation of messages from the queue and the waiting loop on the channel.
   */
  public function start() {
    /*
     * queue:         The queue to consume from.
     * consumer_tag:  The ID for our consumer.
     * no_local:      Don't receive messages published by this consumer (false).
     * no_ack:        Messages will be acknowlegded.
     * exclusive:     Every consumer can access this queue. Crucial for scaling.
     * nowait:
     * callback:      Our consumer function to process the messages. Subclasses have to implement it.
     */
    $this->channel->basic_consume($this->queue, $this->consumerTag, false, false, false, false, array($this, 'processMessage'));
    // Loop and wait for messages.
    while(count($this->channel->callbacks)) {
      $this->channel->wait();
    }
  }

  /**
   * Closes connection and channel.
   */
  public function shutdown() {
    $this->channel->close();
    $this->connection->close();
  }
}
