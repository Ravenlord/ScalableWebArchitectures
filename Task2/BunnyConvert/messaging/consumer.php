<?php
require('../constants.php');
require('../config.php');

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Basic consumer superclass.
 *
 * @author Markus Deutschl <deutschl.markus@gmail.com>
 */
abstract class Consumer {
  protected $channel;
  protected $queue;
  protected $consumerTag;

  /**
   * Constructor for the consumer.
   * @param \PhpAmqpLib\Channel\AMQPChannel $channel The channel associated with the AMQP connection.
   * @param type $queue The queue to consume from.
   * @param type $consumerTag The consumer tag to identify the consumer process.
   */
  public function __construct(\PhpAmqpLib\Channel\AMQPChannel $channel, $queue, $consumerTag) {
    $this->channel = $channel;
    $this->queue = $queue;
    $this->consumerTag = $consumerTag;
  }

  /**
   * Processes messages from the queue. All Subclasses provide their implementations.
   * @param \PhpAmqpLib\Message\AMQPMessage $msg The message to process.
   * @return null No return value.
   */
  public abstract function processMessage($msg);

  /**
   * Starts the consumation of messages from the queue.
   * @return \Consumer
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
    return $this;
  }
}

?>
