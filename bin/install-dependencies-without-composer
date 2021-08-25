#!/usr/bin/env php
<?php

//$composerJson = json_decode(
//    file_get_contents(__DIR__.'/../composer.json'),
//    true
//);

$vendorDir = str_replace(" ", "\\ ", dirname(__DIR__)).'/vendor';
$dependencies = array(
    'psr/http-message' => 'https://github.com/php-fig/http-message/archive/master.zip',
    'psr/log' => 'https://github.com/php-fig/log/archive/refs/tags/1.1.4.zip',
    'psr/simple-cache' => 'https://github.com/php-fig/simple-cache/archive/master.zip',
);

if (!class_exists('ZipArchive') || !function_exists('curl_init')) {
    echo "\nZipArchive and Curl are required to execute this script.\n";
    exit(50);
}

if (is_dir($vendorDir)) {
    passthru('rm -rf '.$vendorDir.'/*');
} else {
    passthru('mkdir '.$vendorDir);
}

$tmpZip = $vendorDir.'/tmp.zip';
foreach ($dependencies as $name => $zipUrl) {
    $dest = $vendorDir.'/'.$name;
    passthru('mkdir -p '.$dest);

    downloadZipFile($zipUrl, $tmpZip);

    $zipBaseName = rtrim(basename($zipUrl), '.zip');
    extractZipFile($tmpZip, $dest, $zipBaseName);
}

passthru('rm -rf '.$tmpZip);

echo "\nDependencies are downloaded inside the $vendorDir folder.";
echo "\nRequire the autoload.php file at the root folder to start using the lib.\n";

exit (0);

function downloadZipFile($url, $destinationFilePath) {
    $fp = fopen($destinationFilePath, 'w+');
    $ch = curl_init($url);

//    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FILE, $fp);

    $exec = curl_exec($ch);

    curl_close($ch);
    fclose($fp);

    if (false === $exec) {
        echo curl_error();
        exit(51);
    }
}

function extractZipFile($zipFileName, $destinationFolderPath, $zipBasename = 'master') {
    $folderName = basename($destinationFolderPath).'-'.$zipBasename;
    $tmpFolder = dirname($zipFileName);
    $zip = new ZipArchive;
    $opened = $zip->open($zipFileName);

    if (true !== $opened) {
        echo "\nCouldn't open $zipFileName\n";
        echo $zip->getStatusString();
        exit(52);
    }

    $zip->extractTo($tmpFolder);
    $zip->close();

    rename($tmpFolder.'/'.$folderName, $destinationFolderPath);
}
