<?php

require(__DIR__ . '/constants.php');
require(__DIR__ . '/config.php');

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;


$allowedExtensions = array('wav', 'wv', 'flac');
$response['error'] = false;
$response['message'] = 'Your file has been successfully uploaded!';
if (!empty($_COOKIE[COOKIE_UID]) && strlen($_COOKIE[COOKIE_UID]) == MD5_LENGTH) {
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
        $uploadBaseFolder = FILE_PATH . $_COOKIE[COOKIE_UID] . DIRECTORY_SEPARATOR;
        // If the folder doesn't exist, create it
        $returnCode = file_exists($uploadBaseFolder);
        if(!$returnCode){
          $returnCode = mkdir($uploadBaseFolder);
        }
        // Create a new unique directory for the file upload.
        date_default_timezone_set('UTC');
        $uploadFileFolder = $uploadBaseFolder . date_timestamp_get(date_create()) . DIRECTORY_SEPARATOR;
        $returnCode = mkdir($uploadFileFolder);
        // Move the uploaded file to the newly created folder.
        $returnCode = move_uploaded_file($fileInfo['tmp_name'], $uploadFileFolder . $fileInfo['name']);
        // If the new folder exists now, continue.
        if($returnCode){
          //TODO: pass message to WAV converter.
          $artist = !empty($_POST[TAGS_ARTIST]) ? $_POST[TAGS_ARTIST] : '';
          $title = !empty($_POST[TAGS_TITLE]) ? $_POST[TAGS_TITLE] : '';
          $album = !empty($_POST[TAGS_ALBUM]) ? $_POST[TAGS_ALBUM] : '';
          $trackno = !empty($_POST[TAGS_TRACKNO]) ? $_POST[TAGS_TRACKNO] : '';
          $year = !empty($_POST[TAGS_YEAR]) ? $_POST[TAGS_YEAR] : '';
          $tags = array(
                          TAGS_ARTIST => $artist,
                          TAGS_TITLE => $title,
                          TAGS_ALBUM => $album,
                          TAGS_TRACKNO => $trackno,
                          TAGS_YEAR => $year
                        );
          $msgBody = json_encode(array(
                                        SOURCE_PATH => $uploadFileFolder,
                                        FILE_NAME => $fileName,
                                        SOURCE_FORMAT => $extension,
                                        TAGS => $tags
                                       ), JSON_UNESCAPED_SLASHES);
          $msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain', 'delivery_mode' => 2));
          $connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
          $channel = $connection->channel();
          // Register the generic shutdown function to ensure the connection and the channel get closed on exit.
          register_shutdown_function('shutdownMessaging', $channel, $connection);
          $channel->basic_publish($msg, WAV_EXCHANGE);
        } else {
          $response['error'] = true;
          $response['message'] = 'Your file could not be moved. Please try again.';
        }
      } else {
        $response['error'] = true;
        $response['message'] = 'Invalid file type. Allowed types: FLAC, WAV, WAVPACK.';
      }
    } else {
      $response['error'] = true;
      $response['message'] = 'There was an error during the upload. Please try again.';
    }

  } else {
    $response['error'] = true;
    $response['message'] = 'Please select a file to upload!';
  }
} else {
  $response['error'] = true;
  $response['message'] = 'Cookies are disabled in your browser. Please enable them to make this service work!';
}
header('Content-type: application/json');
echo json_encode($response);
?>
