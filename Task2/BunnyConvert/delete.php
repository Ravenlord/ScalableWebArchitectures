<?php
$dir = __DIR__ . '/files' . DIRECTORY_SEPARATOR . '7f8496794f5a07ed50db261c70a47ec2';
$it = new RecursiveDirectoryIterator($dir);
$files = new RecursiveIteratorIterator($it,
             RecursiveIteratorIterator::CHILD_FIRST);
foreach($files as $file) {
    if ($file->getFilename() === '.' || $file->getFilename() === '..') {
        continue;
    }
    if ($file->isDir()){
        rmdir($file->getRealPath());
    } else {
        unlink($file->getRealPath());
    }
}
rmdir($dir);
?>