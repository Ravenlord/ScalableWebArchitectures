<?php
require(__DIR__.'/consumer.php');

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connect to RabbitMQ.
$connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$channel = $connection->channel();
// Register shutdown function to shut down messaging properly.
register_shutdown_function('shutdownMessaging', $channel, $connection);

/*
 * Consume messages from our queue.
 * queue:         The queue to consume from.
 * consumer_tag:  The ID for our WAV converter.
 * no_local:      Don't receive messages published by this consumer (false).
 * no_ack:        Messages will be acknowlegded.
 * exclusive:     Every consumer can access this queue. Crucial for scaling.
 * nowait:
 * callback:      Our consumer function to process the messages.
*/
//$channel->basic_consume(WAV_QUEUE, WAV_CONVERTER_CONSUMER_TAG, false, false, false, false, 'processMessage');
/**
 * The WavConverter class. Converts the incoming files to WAV files and messages fileService + other converters.
 * @author Markus Deutschl <deutschl.markus@gmail.com>
 */
class WavConverter extends Consumer {
  public function __construct(PhpAmqpLib\Channel\AMQPChannel $channel, $queue, $consumerTag) {
    parent::__construct($channel, $queue, $consumerTag);
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

// Start the WAV converter with a unique consumer tag.
$wavConverter = new WavConverter($channel, WAV_QUEUE, WAV_CONVERTER_CONSUMER_TAG . '_' . getmypid());
$wavConverter->start();
// Loop and wait for messages.
while(count($channel->callbacks)) {
  $channel->wait();
}
?>
