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
echo '<!DOCTYPE html><html><head><meta charset=utf-8 /><title>BunnyConvert</title><link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css" /><link rel="stylesheet" type="text/css" href="style/main.css" /></head><body>';
echo '<div id="container" class="container"><h1>Welcome to BunnyConvert!</h1>';
echo '<noscript class="badge badge-important"><i class="icon-white icon-remove"></i>Warning! This application needs JavaScript enabled in order to work. Please enable it in your Browser.</noscript>';
echo '<div id="application-message" class="badge badge-important"></div>';
echo '<div id="content">';
echo '<div id="files">';
echo '<strong>' . $uid . '</strong>';
if (empty($folders)) {
  echo 'No files processed yet.';
} else {
  echo '<table id="file-table"><tr><th>File</th><th>Folder</th></tr>';
  foreach ($folders as $subFolder) {
    $files = scandir($path . '/' . $subFolder);
    $files = cleanFileArray($files);
    if (!empty($files)) {
      foreach ($files as $file) {
        echo '<tr><td>' . $file . '</td><td>' . $subFolder . '</td></tr>';
      }
    }
  }
  echo '</table>';
}
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
echo '<label class="control-label" for="artist">Artist</label>';
echo '<div class="controls">';
echo '<input id="artist" class="input-large" name="artist" type="text">';
echo '</div>';
echo '</div>';
echo '<div class="control-group">';
echo '<label class="control-label" for="title">Title</label>';
echo '<div class="controls">';
echo '<input id="title" class="input-large" name="title" type="text">';
echo '</div>';
echo '</div>';
echo '<div class="control-group">';
echo '<label class="control-label" for="album">Album</label>';
echo '<div class="controls">';
echo '<input id="album" class="input-large" name="album" type="text">';
echo '</div>';
echo '</div>';
echo '<div class="control-group">';
echo '<label class="control-label" for="trackno">Track number</label>';
echo '<div class="controls">';
echo '<input id="trackno" class="input-large" name="trackno" type="text">';
echo '</div>';
echo '</div>';
echo '<div class="control-group">';
echo '<label class="control-label" for="year">Year</label>';
echo '<div class="controls">';
echo '<input id="year" class="input-large" name="year" type="text">';
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
?>