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
      case WEBSOCKET_COMMAND_DECODE:
        // WAV conversion
        $success = $data[WEBSOCKET_SUCCESS];
        $message = $success ? 'WAV conversion succeeded.' : 'WAV conversion error.';
        // Pass message to WebSocket for client notification.
        $this->wrenchClient->sendData(
                                      json_encode(array(
                                                        CLIENT_ID         =>  $data[CLIENT_ID],
                                                        WEBSOCKET_COMMAND =>  WEBSOCKET_COMMAND_DECODE,
                                                        WEBSOCKET_SUCCESS =>  $success,
                                                        WEBSOCKET_MESSAGE =>  $message,
                                                        FILE_TARGET       =>  FILE_PATH_WEB . $data[CLIENT_ID] . DIRECTORY_SEPARATOR . $data[SUB_FOLDER] . $data[FILE_NAME],
                                                        FILE_NAME         =>  $data[FILE_NAME],
                                                        SUB_FOLDER        =>  trim($data[SUB_FOLDER], '/')
                                                        ), JSON_UNESCAPED_SLASHES));
        break;
      case WEBSOCKET_COMMAND_ENCODE:
        $success = $data[WEBSOCKET_SUCCESS];
        $message = 'Conversion to ' . $data[TARGET_FORMAT];
        $message .= $success ? ' succeeded.' : ' failed.';
        $this->wrenchClient->sendData(
                                      json_encode(array(
                                                        CLIENT_ID         =>  $data[CLIENT_ID],
                                                        WEBSOCKET_COMMAND =>  WEBSOCKET_COMMAND_ENCODE,
                                                        WEBSOCKET_SUCCESS =>  $success,
                                                        WEBSOCKET_MESSAGE =>  $message,
                                                        FILE_TARGET       =>  FILE_PATH_WEB . $data[CLIENT_ID] . DIRECTORY_SEPARATOR . $data[SUB_FOLDER] . $data[FILE_NAME],
                                                        FILE_NAME         =>  $data[FILE_NAME],
                                                        SUB_FOLDER        =>  trim($data[SUB_FOLDER], '/'),
                                                        TARGET_FORMAT     =>  $data[TARGET_FORMAT]
                                                        ), JSON_UNESCAPED_SLASHES));
        break;
      case WEBSOCKET_COMMAND_DELETE:
        $deletedFolders = $this->deleteFolders();
        //Notify all clients, who had deletes.
        foreach ($deletedFolders as $client => $folders) {
          echo 'Deleted folders of client: ' . $client . "\n\n";
          $this->wrenchClient->sendData(
                                        json_encode(array(
                                                          CLIENT_ID         =>  $client,
                                                          WEBSOCKET_COMMAND =>  WEBSOCKET_COMMAND_DELETE,
                                                          FOLDERS           =>  $folders
                                                          )));
        }
        break;
      default:
        echo 'Unrecognized command.\n';
    }
  }

  /**
   * Checks for folders in the upload directory to delete and deletes them.
   * @return array Associative array of userId => array of deleted folders.
   */
  private function deleteFolders() {
    $now = time();
    $foldersToDelete = array();
    $it = new DirectoryIterator(FILE_PATH);
    // First level iteration (UID).
    foreach ($it as $file) {
      if($file->isDot()) continue;
      if($file->isDir()) {
        $uid = $file->getFilename();
        // Second level iteration (Folders with converted files).
        $subDirIterator = new DirectoryIterator($file->getPathname());
        foreach ($subDirIterator as $subDir) {
          if($subDir->isDot()) continue;
          // Check if the modified time of a folder is older than the expiry time.
          if($subDir->isDir() && ($now - $subDir->getMTime()) > FILE_EXPIRY_TIME) {
            // Add it to the return array and delete contents + the folder itself
            $foldersToDelete[$uid][] = $subDir->getFilename();
            $fileIterator = new DirectoryIterator($subDir->getPathname());
            foreach ($fileIterator as $fileToDelete) {
              if($fileToDelete->isDot()) continue;
              unlink($fileToDelete->getPathname());
            }
            rmdir($subDir->getPathname());
          }
        }
        // Check if the UID folder is older than the expiry time and delete it if so.
        if(($now - $file->getMTime()) > FILE_EXPIRY_TIME) {
          rmdir($file->getPathname());
        }
      }
    }
    return $foldersToDelete;
  }
}

// Start the WAV converter with a unique consumer tag.
$fileService = new FileService(HOST, PORT, USER, PASS, VHOST, FILE_SERVICE_QUEUE, FILE_SERVICE_CONSUMER_TAG . '_' . getmypid());
$fileService->start();
