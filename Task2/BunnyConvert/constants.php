<?php
// Cookie name for the unique user identifier.
define('COOKIE_UID', 'BCUID');
// Lenght of a MD5 hash for checks.
define('MD5_LENGTH', 32);
// The base path for file uploads.
define('FILE_PATH', 'files/');
// The file identifier in the $_FILES superglobal.
define('FILE_FORM_FIELD', 'file');
// File format constants.
define('FILE_FORMAT_FLAC', 'flac');
define('FILE_FORMAT_MP3', 'mp3');
define('FILE_FORMAT_WAV', 'wav');
define('FILE_FORMAT_WAVPACK', 'wv');
// Exchange and queue names.
define('WAV_EXCHANGE', 'wav_exchange');
define('WAV_QUEUE', 'wav_queue');
// Messaging constants for the fields in the message.
define('FILE_NAME', 'file_name');
define('SOURCE_FORMAT', 'source_format');
define('SOURCE_PATH', 'source_path');
define('TAGS', 'tags');
define('TAGS_ARTIST', 'artist');
define('TAGS_TITLE', 'title');
define('TAGS_ALBUM', 'album');
define('TAGS_TRACKNO', 'trackno');
define('TAGS_YEAR', 'year');
// Error reporting switches.
ini_set('display_errors',1);
error_reporting(-1);

?>
