<?php
require('../constants.php');
require('../config.php');

use PhpAmqpLib\Connection\AMQPConnection;
$connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$channel = $connection->channel();

// Register the exchange and the queue for WAV conversion and bind them.
/**
  name: WAV_EXCHANGE  // From constants.php.
  type: direct        // Direct, because many consumers can share one queue.
  passive: false      // Not documented. Cargo cult ;)
  durable: false      // The exchange will not survive server restarts. That's why we have a bootstrap script.
  auto_delete: false  // The exchange won't be deleted after the channel is closed.
*/
$channel->exchange_declare(WAV_EXCHANGE, 'direct', false, false, false);
/**
  name: WAV_QUEUE     // From constants.php.
  passive: false
  durable: false      // The queue will not survive server restarts. That's why we have a bootstrap script.
  exclusive: false    // The queue can be accessed in other channels
  auto_delete: false  // The queue won't be deleted after the channel is closed.
*/
$channel->queue_declare(WAV_QUEUE, false, false, false, false);
$channel->queue_bind(WAV_QUEUE, WAV_EXCHANGE);

// Register the exchange and the queue for the file service and bind them.
$channel->exchange_declare(FILE_SERVICE_EXCHANGE, 'direct', false, false, false);
$channel->queue_declare(FILE_SERVICE_QUEUE, false, false, false, false);
$channel->queue_bind(FILE_SERVICE_QUEUE, FILE_SERVICE_EXCHANGE);

//TODO: Declare and bind all other converter queues.

// Close channel and connection on exit.
$channel->close();
$connection->close();
?>
