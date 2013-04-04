<?php
// Cookie name for the unique user identifier.
define('COOKIE_UID', 'BCUID');
// Lenght of a MD5 hash for checks.
define('MD5_LENGTH', 32);
// The base path for file uploads.
define('FILE_PATH', '/var/www/virthosts/heimdall.multimediatechnology.at/BunnyConvert/files/');
define('FILE_PATH_WEB', '/BunnyConvert/files/');
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
define('WAV_EXCHANGE', 'wav_exchange');
define('WAV_QUEUE', 'wav_queue');
define('WAV_CONVERTER_CONSUMER_TAG', 'wav_consumer');
// Messaging constants for the fields in the message.
define('CLIENT_ID', 'client_id');
define('FILE_SERVICE_ID', 'file_service_id');
define('FILE_NAME', 'file_name');
define('FILE_TARGET', 'file_target');
define('SUB_FOLDER', 'sub_folder');
define('SOURCE_FORMAT', 'source_format');
define('SOURCE_PATH', 'source_path');
define('TAGS', 'tags');
define('TAGS_ARTIST', 'artist');
define('TAGS_TITLE', 'title');
define('TAGS_ALBUM', 'album');
define('TAGS_TRACKNO', 'trackno');
define('TAGS_YEAR', 'year');
//----------------------------------------------------------------------------------------------------------------------WebSocket constants.
// Messaging constants for the WebSocket
define('WEBSOCKET_COMMAND', 'command');
define('WEBSOCKET_COMMAND_CONVERT_WAV', 'convert_wav');
define('WEBSOCKET_COMMAND_REGISTER_CLIENT', 'register_client');
define('WEBSOCKET_COMMAND_REGISTER_FILE_SERVICE', 'register_file_service');
define('WEBSOCKET_SUCCESS', 'success');
define('WEBSOCKET_MESSAGE', 'message');
define('WEBSOCKET_CLIENTID', 'clientId');
//----------------------------------------------------------------------------------------------------------------------Converter constants.
define('CODEC_EXE_FLAC', 'flac');
define('FLAC_OPTIONS_DECODE', ' -d -f -s ');
define('CODEX_EXE_WAVPACK_DECODE', 'wvunpack');
define('CODEX_EXE_WAVPACK_ENCODE', 'wavpack');
define('WAVPACK_OPTIONS_DECODE', ' -q -y ');
// Error reporting switches.
ini_set('display_errors',1);
error_reporting(-1);

?>
