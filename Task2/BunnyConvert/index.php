<?php

require('constants.php');

function generateUID() {
  return md5($_SERVER['REMOTE_ADDR'] . microtime(true));
}

function cleanFileArray(array $fileArray) {
  if (count($fileArray) > 2) {
    $fileArray = array_slice($fileArray, 2);
  } else {
    $fileArray = array();
  }
  return $fileArray;
}

// Check for the cookie. If it is not there yet, set it.
if (empty($_COOKIE[COOKIE_UID]) || strlen($_COOKIE[COOKIE_UID]) != MD5_LENGTH) {
  $uid = generateUID();
  setcookie(COOKIE_UID, $uid);
} else {
  $uid = $_COOKIE[COOKIE_UID];
}
unset($_COOKIE);
$path = FILE_PATH . $uid;
$folders = array();
if (file_exists($path)) {
  $folders = scandir($path);
  $folders = cleanFileArray($folders);
}
echo '<!DOCTYPE html><html><head><meta charset=utf-8 /><title>BunnyConvert</title><link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css" /><link rel="stylesheet" type="text/css" href="style/main.css" /><link rel="shortcut icon" href="img/favicon.png" type="image/png"></head><body>';
echo '<div id="container" class="container"><h1>Welcome to BunnyConvert!</h1>';
echo '<noscript class="badge badge-important"><i class="icon-white icon-remove"></i>Warning! This application needs JavaScript enabled in order to work. Please enable it in your Browser.</noscript>';
echo '<div id="application-message" class="badge badge-important"><noscript><i class="icon-white icon-remove"></i>Warning! This application needs JavaScript enabled in order to work. Please enable it in your Browser.</noscript></div>';
echo '<p>Convert your audio files with ease. Simply upload your file (allowed formats: FLAC, WAV, WV) and get it converted to FLAC, WAV, WV and MP3 in an instant. You can also apply tags to your files if you wish so. <strong>BunnyConvert</strong> utilizes up-to-date technology like <strong><a href="http://www.websocket.org/" target="_blank">WebSockets</a></strong>, messaging with <strong><a href="http://www.rabbitmq.com/" target="_blank">RabbitMQ</a></strong> and <strong><a href="https://en.wikipedia.org/wiki/Ajax_%28programming%29" target="_blank">AJAX</a></strong> to provide an awesome and totally asynchronous experience. No page refreshes will be necessary ever again!</p>';
echo '<p>Feel free to convert your files as you wish. They will be available for one hour and are then deleted automatically.</p>';
echo '<div id="content">';
echo '<div id="files">';
echo '<table id="file-table" class="tablesorter table table-striped table-hover"><thead><tr><th class="lockedOrder-asc">File</th><th class="lockedOrder-asc">Folder</th></tr></thead><tbody>';
if (empty($folders)) {
  echo '<tr id="no-files"><td>No files processed yet.</td><td></td></tr>';
} else {
  foreach ($folders as $subFolder) {
    $files = scandir($path . '/' . $subFolder);
    $files = cleanFileArray($files);
    if (!empty($files)) {
      foreach ($files as $file) {
        $filePath = FILE_PATH_WEB . $uid . DIRECTORY_SEPARATOR . $subFolder . DIRECTORY_SEPARATOR . $file;
        echo '<tr class="' . $subFolder . '"><td><a href="' . $filePath . '" target="_blank">' . $file . '</a></td><td>' . $subFolder . '</td></tr>';
      }
    }
  }
}
echo '</tbody></table>';
echo '</div>';
echo '<div id="file-form-wrapper">';
// Print the upload form
echo '<form id="form" class="form-horizontal" enctype="multipart/form-data" action="upload.php" method="post">';
echo '<fieldset id="file-fieldset">';
echo '<legend>File</legend>';
echo '<div class="control-group">';
echo '<label class="control-label" for="file">File</label>';
echo '<div class="controls">';
echo '<input id="file" class="input-large" name="file" type="file">';
echo '</div>';
echo '<progress id="upload-progress" value="0" max="100"></progress>';
echo '<div id="form-message" class="badge"></div>';
echo '</div>';
echo '</fieldset>';
echo '<a id="tags-expander" href="#"><i id="tags-expander-icon" class="icon-arrow-right"></i>Apply Tags</a>';
echo '<fieldset id="tags-fieldset">';
echo '<legend>Tags</legend>';
echo '<div class="control-group">';
echo '<label class="control-label" for="' . TAGS_ARTIST . '">Artist</label>';
echo '<div class="controls">';
echo '<input id="' . TAGS_ARTIST . '" class="input-large" name="' . TAGS_ARTIST . '" type="text">';
echo '</div>';
echo '</div>';
echo '<div class="control-group">';
echo '<label class="control-label" for="' . TAGS_TITLE . '">Title</label>';
echo '<div class="controls">';
echo '<input id="' . TAGS_TITLE . '" class="input-large" name="' . TAGS_TITLE . '" type="text">';
echo '</div>';
echo '</div>';
echo '<div class="control-group">';
echo '<label class="control-label" for="' . TAGS_ALBUM . '">Album</label>';
echo '<div class="controls">';
echo '<input id="' . TAGS_ALBUM . '" class="input-large" name="' . TAGS_ALBUM . '" type="text">';
echo '</div>';
echo '</div>';
echo '<div class="control-group">';
echo '<label class="control-label" for="' . TAGS_TRACKNO . '">Track number</label>';
echo '<div class="controls">';
echo '<input id="' . TAGS_TRACKNO . '" class="input-large" name="' . TAGS_TRACKNO . '" type="text">';
echo '</div>';
echo '</div>';
echo '<div class="control-group">';
echo '<label class="control-label" for="' . TAGS_YEAR . '">Year</label>';
echo '<div class="controls">';
echo '<input id="' . TAGS_YEAR . '" class="input-large" name="' . TAGS_YEAR . '" type="text">';
echo '</div>';
echo '</div>';
echo '<div class="control-group">';
echo '<label class="control-label" for="' . TAGS_GENRE . '">Genre</label>';
echo '<div class="controls">';
echo '<input id="' . TAGS_GENRE . '" class="input-large" name="' . TAGS_GENRE . '" type="text">';
echo '</div>';
echo '</div>';
echo '</fieldset>';
echo '<div class="form-actions">';
echo '<button class="btn btn-large btn-success" type="submit" >Upload</button>';
echo '</div>';
echo '</form>';
// end of upload form
echo '</div>';
echo '</div>';
echo '<div id="debug"></div>';
echo '</div><!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
echo '<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script><script src="script/jquery.cookie.js"></script><script src="script/jquery.form.js"></script><script src="script/jquery.tablesorter.min.js"></script><script src="script/bc.js"></script></body></html>';
