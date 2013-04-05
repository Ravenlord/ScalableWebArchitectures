<?php
require('../constants.php');
require('../config.php');

use PhpAmqpLib\Connection\AMQPConnection;
$connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$channel = $connection->channel();

// Register the exchange and the queue for decoding to WAV and bind them.
/**
  name: DECODER_EXCHANGE  // From constants.php.
  type: direct        // Direct, because many consumers can share one queue.
  passive: false      // Not documented. Cargo cult ;)
  durable: false      // The exchange will not survive server restarts. That's why we have a bootstrap script.
  auto_delete: false  // The exchange won't be deleted after the channel is closed.
*/
$channel->exchange_declare(DECODER_EXCHANGE, 'direct', false, false, false);
/**
  name: DECODER_QUEUE     // From constants.php.
  passive: false
  durable: false      // The queue will not survive server restarts. That's why we have a bootstrap script.
  exclusive: false    // The queue can be accessed in other channels
  auto_delete: false  // The queue won't be deleted after the channel is closed.
*/
$channel->queue_declare(DECODER_QUEUE, false, false, false, false);
$channel->queue_bind(DECODER_QUEUE, DECODER_EXCHANGE);

// Register the exchange and the queue for the file service and bind them.
$channel->exchange_declare(FILE_SERVICE_EXCHANGE, 'direct', false, false, false);
$channel->queue_declare(FILE_SERVICE_QUEUE, false, false, false, false);
$channel->queue_bind(FILE_SERVICE_QUEUE, FILE_SERVICE_EXCHANGE);

// Register the exchange and the queue for the file encoding.
$channel->exchange_declare(ENCODER_EXCHANGE, 'direct', false, false, false);
$channel->queue_declare(ENCODER_QUEUE, false, false, false, false);
$channel->queue_bind(ENCODER_QUEUE, ENCODER_EXCHANGE);

// Close channel and connection on exit.
$channel->close();
$connection->close();
?>
