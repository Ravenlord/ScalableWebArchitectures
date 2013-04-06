<?php
require('../constants.php');
require('../config.php');

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$channel = $connection->channel();
// Register the generic shutdown function to ensure the connection and the channel get closed on exit.
register_shutdown_function('shutdownMessaging', $channel, $connection);
// Simply notify the file service to delete folders.
$msgBody = json_encode(array(WEBSOCKET_COMMAND  =>  WEBSOCKET_COMMAND_DELETE));
$msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain', 'delivery_mode' => 2));
$channel->basic_publish($msg, FILE_SERVICE_EXCHANGE);