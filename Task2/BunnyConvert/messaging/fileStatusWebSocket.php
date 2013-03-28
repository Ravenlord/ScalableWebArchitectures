<?php
require('../constants.php');
require('../config.php');

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class FileStatusNotifier implements MessageComponentInterface {
  private $clients;
  //TODO: Integrate RabbitMQ messages here
  public function __construct() {
    $this->clients = new \SplObjectStorage();
  }

  public function onOpen(ConnectionInterface $conn) {
    echo 'Client ' . $conn->resourceId . " connected.\n";
  }

  public function onMessage(ConnectionInterface $from, $msg) {
    echo "Got Message: " . $msg . " from client: " . $from->resourceId . "\n";
    $msg = json_decode($msg, true);
    if(empty($msg[WEBSOCKET_COMMAND])) {
      echo "Illegal message format.\n";
      return;
    }
    switch ($msg[WEBSOCKET_COMMAND]) {
      case WEBSOCKET_COMMAND_REGISTER:
        $success = true;
        if(empty($msg[WEBSOCKET_CLIENTID]) || strlen($msg[WEBSOCKET_CLIENTID]) != MD5_LENGTH) {
          echo "Illegal client id.\n";
          $success = false;
        } else {
          echo "Registering client " . $from->resourceId . " with BCID " . $msg[WEBSOCKET_CLIENTID] . "\n";
          $this->clients->attach($from, $msg[WEBSOCKET_CLIENTID]);
        }
        $returnMessage = array(WEBSOCKET_COMMAND => WEBSOCKET_COMMAND_REGISTER, WEBSOCKET_SUCCESS => $success);
        $from->send(json_encode($returnMessage));
        break;
      default:
        echo "Unrecognized command.\n";
    }
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo 'Error: ' + $e->getMessage() . "\n";
    $conn->close();
  }

  public function onClose(ConnectionInterface $conn) {
    echo 'Client ' . $conn->resourceId . " disconnected.\n";
    $this->clients->detach($conn);
  }

}
$fileStatusNotifier = new FileStatusNotifier();
$server = IoServer::factory(new WsServer($fileStatusNotifier), 6666);
$server->run();
?>
