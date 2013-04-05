<?php
/**
 * Generic encoder class. Converts WAV files to FLAC, WAVPACK or LAME according to message received.
 *
 * @author Markus Deutschl <deutschl.markus@gmail.com>
 */
class Encoder extends Consumer{
  public function __construct($host, $port, $user, $pass, $vhost, $queue, $consumerTag) {
    parent::__construct($host, $port, $user, $pass, $vhost, $queue, $consumerTag);
  }

  public function processMessage($msg) {

  }
}

?>
