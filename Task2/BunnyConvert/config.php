<?php

require_once __DIR__.'/vendor/autoload.php';

define('HOST', 'localhost');
define('PORT', 5672);
define('USER', 'heimdall');
define('PASS', 'keines');
define('VHOST', '/');

//If this is enabled you can see AMQP output on the CLI
//define('AMQP_DEBUG', true);

// Shutdown function for shutting down connections and channels.
function shutdownMessaging($channel, $connection) {
  $channel->close();
  $connection->close();
}