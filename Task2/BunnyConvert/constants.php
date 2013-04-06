<?php
// Cookie name for the unique user identifier.
define('COOKIE_UID', 'BCUID');
// Lenght of a MD5 hash for checks.
define('MD5_LENGTH', 32);
// The base path for file uploads.
define('FILE_PATH', '/var/www/virthosts/heimdall.multimediatechnology.at/BunnyConvert/files/');
define('FILE_PATH_WEB', '/BunnyConvert/files/');
define('FILE_EXPIRY_TIME', 3600);
// The file identifier in the $_FILES superglobal.
define('FILE_FORM_FIELD', 'file');
// File format constants.
define('FILE_FORMAT_FLAC', 'flac');
define('FILE_FORMAT_MP3', 'mp3');
define('FILE_FORMAT_WAV', 'wav');
define('FILE_FORMAT_WAVPACK', 'wv');
//----------------------------------------------------------------------------------------------------------------------RabbitMQ constants.
//  Exchange and queue names.
define('FILE_SERVICE_EXCHANGE', 'file_service_exchange');
define('FILE_SERVICE_QUEUE', 'file_service_queue');
define('FILE_SERVICE_CONSUMER_TAG', 'file_service_consumer');
define('DECODER_EXCHANGE', 'decoder_exchange');
define('DECODER_QUEUE', 'decoder_queue');
define('DECODER_CONSUMER_TAG', 'decoder_consumer');
define('ENCODER_EXCHANGE', 'encoder_exchange');
define('ENCODER_QUEUE', 'encoder_queue');
define('ENCODER_CONSUMER_TAG', 'encoder_consumer');
// Messaging constants for the fields in the messages.
define('CLIENT_ID', 'client_id');
define('FILE_SERVICE_ID', 'file_service_id');
define('FILE_NAME', 'file_name');
define('FILE_TARGET', 'file_target');
define('FOLDERS', 'folders');
define('SUB_FOLDER', 'sub_folder');
define('SOURCE_FORMAT', 'source_format');
define('SOURCE_PATH', 'source_path');
define('TARGET_FORMAT', 'target_format');
define('TAGS', 'tags');
define('TAGS_ARTIST', 'ARTIST');
define('TAGS_TITLE', 'TITLE');
define('TAGS_ALBUM', 'ALBUM');
define('TAGS_TRACKNO', 'TRACKNUMBER');
define('TAGS_YEAR', 'DATE');
define('TAGS_GENRE', 'GENRE');
//----------------------------------------------------------------------------------------------------------------------WebSocket constants.
// Messaging constants for the WebSocket
define('WEBSOCKET_COMMAND', 'command');
define('WEBSOCKET_COMMAND_KEEPALIVE', 'keepalive');
define('WEBSOCKET_COMMAND_DECODE', 'decode');
define('WEBSOCKET_COMMAND_ENCODE', 'encode');
define('WEBSOCKET_COMMAND_REGISTER_CLIENT', 'register_client');
define('WEBSOCKET_COMMAND_REGISTER_FILE_SERVICE', 'register_file_service');
define('WEBSOCKET_COMMAND_DELETE', 'delete');
define('WEBSOCKET_SUCCESS', 'success');
define('WEBSOCKET_MESSAGE', 'message');
define('WEBSOCKET_CLIENTID', 'clientId');
//----------------------------------------------------------------------------------------------------------------------Converter constants.
define('CODEC_EXE_FLAC', 'flac');
define('FLAC_OPTIONS_DECODE', ' -d -f --totally-silent ');
define('FLAC_OPTIONS_ENCODE', ' -f --totally-silent -V -8 ');
define('CODEC_EXE_WAVPACK_DECODE', 'wvunpack');
define('CODEC_EXE_WAVPACK_ENCODE', 'wavpack');
define('WAVPACK_OPTIONS_DECODE', ' -q -y ');
define('WAVPACK_OPTIONS_ENCODE', ' -q -y -hh ');
define('CODEC_EXE_MP3', 'lame');
define('MP3_OPTIONS_ENCODE', ' -S -V 0 ');
// Error reporting switches.
ini_set('display_errors',1);
error_reporting(-1);

?>
