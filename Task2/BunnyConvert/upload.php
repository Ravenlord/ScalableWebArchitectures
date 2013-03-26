<?php
require(__DIR__.'/constants.php');
require(__DIR__.'/config.php');

//use PhpAmqpLib\Connection\AMQPConnection;
//
//$connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$allowedExtensions = array('wav', 'wv', 'flac');
$response['error'] = false;
if(!empty($_COOKIE[COOKIE_UID]) && strlen($_COOKIE[COOKIE_UID]) == MD5_LENGTH) {
    // Check if we have the file information needed.
    if(!empty($_FILES[FILE_FORM_FIELD]) && !empty($_FILES[FILE_FORM_FIELD]['name'])){
        $fileInfo = $_FILES[FILE_FORM_FIELD];
        // Evaluate file extension
        $pos = strrpos($fileInfo['name'], '.');
        $fileName = substr($fileInfo['name'], 0, $pos);
        $extension = substr($fileInfo['name'], $pos+1);
        if(in_array($extension, $allowedExtensions)) {
            // Construct path for upload base folder.
            $uploadBaseFolder = FILE_PATH . $_COOKIE[COOKIE_UID] . DIRECTORY_SEPARATOR;
            // TODO
            //<---------------------------------------------------------------------
        } else {
            $response['error'] = true;
            $response['errorText'] = 'Invalid file type. Allowed types: FLAC, WAV, WAVPACK.';
        }
    } else {
       $response['error'] = true;
       $response['errorText'] = 'Please select a file to upload!'; 
    }
    
} else {
    $response['error'] = true;
    $response['errorText'] = 'Cookies are disabled in your browser. Please enable them to make this service work!';
}
echo json_encode($response);
echo var_dump($_POST);
echo '<br>';
echo var_dump($_FILES);
echo $_COOKIE[COOKIE_UID];
echo $fileName . '<br>' . $extension;

?>
