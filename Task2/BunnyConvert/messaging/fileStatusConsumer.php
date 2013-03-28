<?php
require('../constants.php');
require('../config.php');

use Wrench\Client;

$wrenchClient = new Client('ws://heimdall.multimediatechnology.at:6666/', 'ws://heimdall.multimediatechnology.at:6666/');
$wrenchClient->connect();
$wrenchClient->sendData('Test');
//TODO: implement RabbitMQ consumer for converter messages and send them to the fileStatusWebSocketServer
?>
