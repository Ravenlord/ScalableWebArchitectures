<?php

require(__DIR__ . '/constants.php');
require(__DIR__ . '/config.php');

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$allowedExtensions = array(FILE_FORMAT_WAV, FILE_FORMAT_WAVPACK, FILE_FORMAT_FLAC);
$response[WEBSOCKET_SUCCESS] = true;
$response[WEBSOCKET_MESSAGE] = 'Your file has been successfully uploaded!';
if (!empty($_COOKIE[COOKIE_UID]) && strlen($_COOKIE[COOKIE_UID]) == MD5_LENGTH) {
  $clientId = $_COOKIE[COOKIE_UID];
  // Check if we have the file information needed.
  if (!empty($_FILES[FILE_FORM_FIELD]) && !empty($_FILES[FILE_FORM_FIELD]['name'])) {
    $fileInfo = $_FILES[FILE_FORM_FIELD];
    // Check if there was an upload error
    if (!$fileInfo['error']) {
      // Evaluate file extension
      $pos = strrpos($fileInfo['name'], '.');
      $fileName = substr($fileInfo['name'], 0, $pos);
      $extension = substr($fileInfo['name'], $pos + 1);
      if (in_array($extension, $allowedExtensions)) {
        // Construct path for upload base folder.
        $uploadBaseFolder = FILE_PATH . $clientId . DIRECTORY_SEPARATOR;
        // If the folder doesn't exist, create it and set permissions.
        $returnCode = file_exists($uploadBaseFolder);
        if(!$returnCode){
          $returnCode = mkdir($uploadBaseFolder);
          chmod($uploadBaseFolder, 0777);
        }
        // Create a new unique directory for the file upload and set permissions.
        date_default_timezone_set('UTC');
        $subFolder = date_timestamp_get(date_create()) . DIRECTORY_SEPARATOR;
        $uploadFileFolder = $uploadBaseFolder . $subFolder;
        $returnCode = mkdir($uploadFileFolder);
        chmod($uploadFileFolder, 0777);
        // Move the uploaded file to the newly created folder and set permissionsa.
        $fileUploadName = $uploadFileFolder . $fileInfo['name'];
        $returnCode = move_uploaded_file($fileInfo['tmp_name'], $uploadFileFolder . $fileInfo['name']);
        chmod($fileUploadName, 0777);
        // If the new folder exists now, continue.
        if($returnCode){
          $artist = !empty($_POST[TAGS_ARTIST]) ? $_POST[TAGS_ARTIST] : '';
          $title = !empty($_POST[TAGS_TITLE]) ? $_POST[TAGS_TITLE] : '';
          $album = !empty($_POST[TAGS_ALBUM]) ? $_POST[TAGS_ALBUM] : '';
          $trackno = !empty($_POST[TAGS_TRACKNO]) ? $_POST[TAGS_TRACKNO] : '';
          $year = !empty($_POST[TAGS_YEAR]) ? $_POST[TAGS_YEAR] : '';
          $genre = !empty($_POST[TAGS_GENRE]) ? $_POST[TAGS_GENRE] : '';
          $tags = array(
                          TAGS_ARTIST   =>  $artist,
                          TAGS_TITLE    =>  $title,
                          TAGS_ALBUM    =>  $album,
                          TAGS_TRACKNO  =>  $trackno,
                          TAGS_YEAR     =>  $year,
                          TAGS_GENRE    =>  $genre
                        );
          $msgBody = json_encode(array(
                                        CLIENT_ID     =>  $clientId,
                                        SOURCE_PATH   =>  $uploadFileFolder,
                                        SUB_FOLDER    => $subFolder,
                                        FILE_NAME     =>  $fileName,
                                        SOURCE_FORMAT =>  $extension,
                                        TAGS          =>  $tags
                                       ), JSON_UNESCAPED_SLASHES);
          $msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain', 'delivery_mode' => 2));
          $connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
          $channel = $connection->channel();
          // Register the generic shutdown function to ensure the connection and the channel get closed on exit.
          register_shutdown_function('shutdownMessaging', $channel, $connection);
          $channel->basic_publish($msg, DECODER_EXCHANGE);
        } else {
          $response[WEBSOCKET_SUCCESS] = false;
          $response[WEBSOCKET_MESSAGE] = 'Your file could not be moved. Please try again.';
        }
      } else {
        $response[WEBSOCKET_SUCCESS] = false;
        $response[WEBSOCKET_MESSAGE] = 'Invalid file type. Allowed types: FLAC, WAV, WAVPACK.';
      }
    } else {
      $response[WEBSOCKET_SUCCESS] = false;
      $response[WEBSOCKET_MESSAGE] = 'There was an error during the upload. Please try again.';
    }

  } else {
    $response[WEBSOCKET_SUCCESS] = false;
    $response[WEBSOCKET_MESSAGE] = 'Please select a file to upload!';
  }
} else {
  $response[WEBSOCKET_SUCCESS] = false;
  $response[WEBSOCKET_MESSAGE] = 'Cookies are disabled in your browser. Please enable them to make this service work!';
}
header('Content-type: application/json');
echo json_encode($response);
?>
