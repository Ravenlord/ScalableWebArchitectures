<?php
require(__DIR__.'/consumer.php');

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Generic encoder class. Converts WAV files to FLAC, WAVPACK or LAME according to the message received.
 *
 * @author Markus Deutschl <deutschl.markus@gmail.com>
 */
class Encoder extends Consumer{
  public function __construct($host, $port, $user, $pass, $vhost, $queue, $consumerTag) {
    parent::__construct($host, $port, $user, $pass, $vhost, $queue, $consumerTag);
  }

  public function processMessage($msg) {
    echo "Got message:\n" . $msg->body . "\n\n";
    // Acknowledge the message.
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    $data = json_decode($msg->body, true);
    $success = false;
    // Encode file according to target format.
    call_user_func(array($this, $data[TARGET_FORMAT] . 'Encode'), $data[SOURCE_PATH], $data[FILE_NAME] . '.' . FILE_FORMAT_WAV, $data[TAGS]);
    // Check if the encoded file is available.
    $fileName = $data[FILE_NAME] . '.' . $data[TARGET_FORMAT];
    if(file_exists($data[SOURCE_PATH] . $fileName)) {
      $success = true;
    }
    // Pass message to fileService according to success.
    $msgBody = json_encode(array(
                                  CLIENT_ID           =>  $data[CLIENT_ID],
                                  WEBSOCKET_COMMAND   =>  WEBSOCKET_COMMAND_ENCODE,
                                  WEBSOCKET_SUCCESS   =>  $success,
                                  FILE_NAME           =>  $fileName,
                                  SUB_FOLDER          =>  $data[SUB_FOLDER],
                                  TARGET_FORMAT       =>  $data[TARGET_FORMAT]
                                 ), JSON_UNESCAPED_SLASHES);
    $msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain', 'delivery_mode' => 2));
    $this->channel->basic_publish($msg, FILE_SERVICE_EXCHANGE);
  }

  public function flacEncode($sourcePath, $fileName, array $tags) {
    $command = CODEC_EXE_FLAC . FLAC_OPTIONS_ENCODE;
    foreach ($tags as $field => $value) {
      $command .= '-T ' . $field . '="' . $value . '" ';
    }
    $command .= $sourcePath . $fileName;
    echo "Executing: $command\n\n";
    shell_exec($command);
  }

  public function wvEncode($sourcePath, $fileName, array $tags) {
    $command = CODEC_EXE_WAVPACK_ENCODE . WAVPACK_OPTIONS_ENCODE;
    foreach ($tags as $field => $value) {
      $command .= '-w ' . $field . '="' . $value .'" ';
    }
    $command .= $sourcePath . $fileName;
    echo "Executing: $command\n\n";
    shell_exec($command);
  }

  public function mp3Encode($sourcePath, $fileName, array $tags) {
    $command = CODEC_EXE_MP3 . MP3_OPTIONS_ENCODE;
    $command .= !empty($tags[TAGS_ARTIST]) ? '--ta "' . $tags[TAGS_ARTIST] . '" ' : '';
    $command .= !empty($tags[TAGS_TITLE]) ? '--tt "' . $tags[TAGS_TITLE] . '" ' : '';
    $command .= !empty($tags[TAGS_ALBUM]) ? '--tl "' . $tags[TAGS_ALBUM] . '" ' : '';
    $command .= !empty($tags[TAGS_TRACKNO]) ? '--tn "' . $tags[TAGS_TRACKNO] . '" ' : '';
    $command .= !empty($tags[TAGS_YEAR]) ? '--ty "' . $tags[TAGS_YEAR] . '" ' : '';
    $command .= !empty($tags[TAGS_GENRE]) ? '--tg "' . $tags[TAGS_GENRE] . '" ' : '';
    $command .= $sourcePath . $fileName;
    echo "Executing: $command\n\n";
    shell_exec($command);
  }
}

$encoder = new Encoder(HOST, PORT, USER, PASS, VHOST, ENCODER_QUEUE, ENCODER_CONSUMER_TAG . '_' . getmypid());
$encoder->start();