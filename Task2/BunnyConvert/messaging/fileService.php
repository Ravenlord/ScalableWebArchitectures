<?php
require(__DIR__ . '/consumer.php');

use Wrench\Client;

class FileService extends Consumer {
  protected $wrenchClient;

  public function __construct($host, $port, $user, $pass, $vhost, $queue, $consumerTag) {
    parent::__construct($host, $port, $user, $pass, $vhost, $queue, $consumerTag);
    // Connect to the WebSocket server.
    $this->wrenchClient = new Client('ws://heimdall.multimediatechnology.at:6666/', 'ws://heimdall.multimediatechnology.at:6666/');
    $this->wrenchClient->connect();
    // Register on the WebSocket server.
    $this->wrenchClient->sendData(json_encode(array(
                                                    WEBSOCKET_COMMAND =>  WEBSOCKET_COMMAND_REGISTER_FILE_SERVICE,
                                                    FILE_SERVICE_ID   =>  $this->consumerTag
                                                    ), JSON_UNESCAPED_SLASHES));
  }

  public function processMessage($msg) {
    echo "FileService received:\n";
    echo $msg->body . "\n\n";
    // Acknowledge the message.
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    $data = json_decode($msg->body, true);
    // Take action according to command.
    $success = true;
    $message = '';
    switch ($data[WEBSOCKET_COMMAND]) {
      case WEBSOCKET_COMMAND_CONVERT_WAV:
        // WAV conversion
        $success = $data[WEBSOCKET_SUCCESS];
        $message = $success ? 'WAV conversion succeeded.' : 'WAV conversion error.';
        // Pass message to WebSocket for client notification.
        $this->wrenchClient->sendData(
                                      json_encode(array(
                                                        CLIENT_ID         =>  $data[CLIENT_ID],
                                                        WEBSOCKET_COMMAND =>  WEBSOCKET_COMMAND_CONVERT_WAV,
                                                        WEBSOCKET_SUCCESS =>  $success,
                                                        WEBSOCKET_MESSAGE =>  $message,
                                                        FILE_TARGET       =>  FILE_PATH_WEB . $data[CLIENT_ID] . DIRECTORY_SEPARATOR . $data[SUB_FOLDER] . $data[FILE_NAME],
                                                        FILE_NAME         =>  $data[FILE_NAME],
                                                        SUB_FOLDER        =>  trim($data[SUB_FOLDER], '/')
                                                        ), JSON_UNESCAPED_SLASHES));
        break;
      default:
        $success = false;
        $message = 'Unrecognized command.';
    }
  }
}

// Start the WAV converter with a unique consumer tag.
$fileService = new FileService(HOST, PORT, USER, PASS, VHOST, FILE_SERVICE_QUEUE, FILE_SERVICE_CONSUMER_TAG . '_' . getmypid());
$fileService->start();
