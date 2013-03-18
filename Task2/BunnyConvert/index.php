<?php
require('constants.php');
function generateUID() {
    return md5($_SERVER['REMOTE_ADDR'] . microtime(true));
}
function cleanFileArray(array $fileArray) {
    if(count($fileArray) > 2) {
        $fileArray = array_slice($fileArray, 2);
    } else {
        $fileArray = array();
    }
    return $fileArray;
}
// Check for the cookie. If it is not there yet, set it.
if(empty($_COOKIE[COOKIE_UID]) || strlen($_COOKIE[COOKIE_UID]) != MD5_LENGTH) {
    $uid = generateUID();
    setcookie(COOKIE_UID, $uid);
} else {
    $uid = $_COOKIE[COOKIE_UID];
}
unset($_COOKIE);
$path = FILE_PATH . $uid;
$folders = array();
if(file_exists($path)) {
    $folders = scandir($path);
    $folders = cleanFileArray($folders);
}
echo '<!DOCTYPE html><html><head><meta charset=utf-8 /><title>BunnyConvert</title><link rel="stylesheet" type="text/css" href="style/main.css" /></head><body>';
echo '<div id="container"><h1>Welcome to BunnyConvert!</h1>';
echo '<noscript>Warning! This application needs JavaScript enabled in order to work. Please enable it in your Browser.</noscript>';
echo '<div id="content">';
echo '<div id="files">';
if(empty($folders)) {
    echo 'No files processed yet.';
} else {
    echo '<ul id="file-list">';
    foreach ($folders as $subFolder) {
        $files = scandir($path . '/' . $subFolder);
        $files = cleanFileArray($files);
        echo '<li id="folder-' . $subFolder . '">' . $subFolder;
        if(!empty($files)) {
            echo '<ul id="folder-' . $subFolder . '-files">';
            foreach ($files as $file) {
                echo '<li>' . $file . '</li>';
            }
            echo '</ul>';
        }
        echo '</li>';
    }
    echo '</ul>';
}
echo '</div>';
echo '</div>';
echo '</div><!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]--><script src="http://code.jquery.com/jquery-1.9.1.min.js"></script><script src="script/bc.js"></script></body></html>';
?>