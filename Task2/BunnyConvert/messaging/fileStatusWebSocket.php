<?php
require('../constants.php');
require('../config.php');

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class FileStatusNotifier implements MessageComponentInterface {
  private $clients;
  private $fileServices;
  //TODO: Integrate RabbitMQ messages here
  public function __construct() {
    $this->clients = new \SplObjectStorage();
    $this->fileServices = new \SplObjectStorage();
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
      case WEBSOCKET_COMMAND_REGISTER_CLIENT:
        $success = true;
        if(empty($msg[WEBSOCKET_CLIENTID]) || strlen($msg[WEBSOCKET_CLIENTID]) != MD5_LENGTH) {
          echo "Illegal client id.\n";
          $success = false;
        } else {
          echo "Registering client " . $from->resourceId . " with BCID " . $msg[WEBSOCKET_CLIENTID] . "\n";
          $this->clients->attach($from, $msg[WEBSOCKET_CLIENTID]);
        }
        $returnMessage = array(WEBSOCKET_COMMAND => WEBSOCKET_COMMAND_REGISTER_CLIENT, WEBSOCKET_SUCCESS => $success);
        $from->send(json_encode($returnMessage));
        break;
      case WEBSOCKET_COMMAND_KEEPALIVE:
        echo 'Received keepalive from client ' . $from->resourceId . "\n";
        $from->send(json_encode($msg));
        break;
      case WEBSOCKET_COMMAND_REGISTER_FILE_SERVICE:
        $this->fileServices->attach($from, $msg[FILE_SERVICE_ID]);
        echo "Registering file service " . $from->resourceId . " with ID " . $msg[FILE_SERVICE_ID] . "\n";
        break;
      case WEBSOCKET_COMMAND_CONVERT_WAV:
        $this->notifyClient($msg[CLIENT_ID], $msg);
        break;
      default:
        echo "Unrecognized command.\n";
    }
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo 'Error: ' + $e->getMessage() . "\n";
    $this->clients->detach($conn);
    $this->fileServices->detach($conn);
    $conn->close();
  }

  public function onClose(ConnectionInterface $conn) {
    echo 'Client ' . $conn->resourceId . " disconnected.\n";
    $this->clients->detach($conn);
    $this->fileServices->detach($conn);
  }

  private function notifyClient($clientId, $message) {
    foreach ($this->clients as $client) {
      if($this->clients[$client] == $clientId) {
        $client->send(json_encode($message), JSON_UNESCAPED_SLASHES);
        return;
      }
    }
  }

}
$fileStatusNotifier = new FileStatusNotifier();
$server = IoServer::factory(new WsServer($fileStatusNotifier), 6666);
$server->run();
?>
