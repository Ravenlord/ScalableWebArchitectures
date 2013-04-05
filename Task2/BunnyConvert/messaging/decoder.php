<?php
require(__DIR__.'/consumer.php');

use PhpAmqpLib\Message\AMQPMessage;

/**
 * The Decoder class. Converts the incoming files to WAV files and messages fileService + other converters.
 * @author Markus Deutschl <deutschl.markus@gmail.com>
 */
class Decoder extends Consumer {
  public function __construct($host, $port, $user, $pass, $vhost, $queue, $consumerTag) {
    parent::__construct($host, $port, $user, $pass, $vhost, $queue, $consumerTag);
  }

  public function processMessage($msg) {
    echo "Got message:\n";
    echo $msg->body . "\n\n";
    // Acknowledge the message.
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    $data = json_decode($msg->body, true);
    // TODO: sanitizing checks
    switch ($data[SOURCE_FORMAT]) {
      case FILE_FORMAT_FLAC:
        $command = CODEC_EXE_FLAC . FLAC_OPTIONS_DECODE . $data[SOURCE_PATH] . $data[FILE_NAME] . '.' . FILE_FORMAT_FLAC;
        shell_exec($command);
        break;
      case FILE_FORMAT_WAVPACK:
        $command = CODEX_EXE_WAVPACK_DECODE . WAVPACK_OPTIONS_DECODE . $data[SOURCE_PATH] . $data[FILE_NAME] . '.' . FILE_FORMAT_WAVPACK;
        shell_exec($command);
        break;
    }
    $success = false;
    // Check if the converted file is available.
    $fileName = $data[FILE_NAME] . '.' . FILE_FORMAT_WAV;
    if(file_exists($data[SOURCE_PATH] . $fileName)) {
      // TODO: Pass message to other converters
      $success = true;
    }
    // Pass message to fileService according to success
    $msgBody = json_encode(array(
                                  CLIENT_ID           =>  $data[CLIENT_ID],
                                  WEBSOCKET_COMMAND   =>  WEBSOCKET_COMMAND_CONVERT_WAV,
                                  WEBSOCKET_SUCCESS   =>  $success,
                                  FILE_NAME           =>  $fileName,
                                  SUB_FOLDER          =>  $data[SUB_FOLDER]
                                 ), JSON_UNESCAPED_SLASHES);
    $msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain', 'delivery_mode' => 2));
    $this->channel->basic_publish($msg, FILE_SERVICE_EXCHANGE);
  }
}

// Start the decoder with a unique consumer tag.
$wavConverter = new Decoder(HOST, PORT, USER, PASS, VHOST, DECODER_QUEUE, DECODER_CONSUMER_TAG . '_' . getmypid());
$wavConverter->start();
