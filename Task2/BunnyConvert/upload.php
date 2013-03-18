<?php
require(__DIR__.'/constants.php');
require(__DIR__.'/config.php');

use PhpAmqpLib\Connection\AMQPConnection;

$connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);

?>
