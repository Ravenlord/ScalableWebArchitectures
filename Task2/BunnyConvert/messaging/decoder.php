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
    // Call decoder method according to source format.
    if($data[SOURCE_FORMAT] != FILE_FORMAT_WAV) {
      call_user_func(array($this, $data[SOURCE_FORMAT] . 'Decode'), $data[SOURCE_PATH], $data[FILE_NAME]);
    }
    $success = false;
    // Check if the converted file is available.
    $fileName = $data[FILE_NAME] . '.' . FILE_FORMAT_WAV;
    if(file_exists($data[SOURCE_PATH] . $fileName)) {
      // Pass message for every target format encoder queue.
      $msgOptions = array('content_type' => 'text/plain', 'delivery_mode' => 2);
      // FLAC.
      $msgBody = array(
                        CLIENT_ID     =>  $data[CLIENT_ID],
                        SOURCE_PATH   =>  $data[SOURCE_PATH],
                        FILE_NAME     =>  $data[FILE_NAME],
                        TAGS          =>  $data[TAGS],
                        SUB_FOLDER    =>  $data[SUB_FOLDER]
                      );
      $encoderFormats = array(FILE_FORMAT_FLAC, FILE_FORMAT_WAVPACK, FILE_FORMAT_MP3);
      foreach ($encoderFormats as $format) {
        $msgBody[TARGET_FORMAT] = $format;
        $msg = new AMQPMessage(json_encode($msgBody, JSON_UNESCAPED_SLASHES), $msgOptions);
        $this->channel->basic_publish($msg, ENCODER_EXCHANGE);
      }
      $success = true;
    }
    // Pass message to fileService according to success.
    $msgBody = json_encode(array(
                                  CLIENT_ID           =>  $data[CLIENT_ID],
                                  WEBSOCKET_COMMAND   =>  WEBSOCKET_COMMAND_DECODE,
                                  WEBSOCKET_SUCCESS   =>  $success,
                                  FILE_NAME           =>  $fileName,
                                  SUB_FOLDER          =>  $data[SUB_FOLDER]
                                 ), JSON_UNESCAPED_SLASHES);
    $msg = new AMQPMessage($msgBody, $msgOptions);
    $this->channel->basic_publish($msg, FILE_SERVICE_EXCHANGE);
  }

  /**
   * Converts the source FLAC file to WAV.
   * @param string $sourcePath  The file path where the file to convert resides.
   * @param string $fileName    The source file name.
   */
  public function flacDecode($sourcePath, $fileName) {
    $command = CODEC_EXE_FLAC . FLAC_OPTIONS_DECODE . $sourcePath . $fileName . '.' . FILE_FORMAT_FLAC;
    shell_exec($command);
  }

  /**
   * Converts the source WAVPACK file to WAV.
   * @param string $sourcePath  The file path where the file to convert resides.
   * @param string $fileName    The source file name.
   */
  public function wvDecode($sourcePath, $fileName) {
    $command = CODEC_EXE_WAVPACK_DECODE . WAVPACK_OPTIONS_DECODE . $sourcePath . $fileName . '.' . FILE_FORMAT_WAVPACK;
    shell_exec($command);
  }
}

// Start the decoder with a unique consumer tag.
$decoder = new Decoder(HOST, PORT, USER, PASS, VHOST, DECODER_QUEUE, DECODER_CONSUMER_TAG . '_' . getmypid());
$decoder->start();
