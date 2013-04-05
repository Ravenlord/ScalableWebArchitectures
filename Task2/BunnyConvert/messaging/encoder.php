<?php
require(__DIR__.'/consumer.php');
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
    echo "Got message:\n";
    echo $msg->body . "\n\n";
    // Acknowledge the message.
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    $data = json_decode($msg->body, true);
    $success = false;
    $filePath = $data[SOURCE_PATH] . $data[FILE_NAME] . '.' . FILE_FORMAT_WAV;
    // Encode file according to target format.
    switch ($data[TARGET_FORMAT]) {
      case FILE_FORMAT_FLAC:
        $command = CODEC_EXE_FLAC . FLAC_OPTIONS_ENCODE;
        foreach ($data[TAGS] as $tag => $value) {
          $command .= '-T ' . $tag . '=' . $value .' ';
        }
        break;
      case FILE_FORMAT_WAVPACK:
        break;
      case FILE_FORMAT_MP3:
        break;
      default:
        echo 'Unrecognized file format.';
    }
  }
}

$encoder = new Encoder(HOST, PORT, USER, PASS, VHOST, ENCODER_QUEUE, ENCODER_CONSUMER_TAG . '_' . getmypid());
$encoder->start();