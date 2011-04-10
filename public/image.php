<?php

define('NO_SESSION_SUPPORT', true);
require "../init.php";

if (empty($_GET['id'])) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

$image = Image::find($_GET['id']);
$image->fileExists = null;
if (!$image->fileExists()) {
    header("HTTP/1.1 404 Not Found");
    echo "File not found: {$image->getPath()}";
    exit;
}

$format = $image->getFormat();
if ($format === null) {
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

$size = "original";
if (isset($_GET['size'])) {
    switch ($_GET['size']) {
        case "tiny":
            $size = "tiny";
            break;
        case "thumb":
            $size = "thumb";
            break;
    }
}

header("Content-Type:image/{$format}");

if ($image->cacheFileExists($size)) {
    
    $lastModified = $image->getLastModified($size);
    if ($lastModified && array_key_exists("HTTP_IF_MODIFIED_SINCE", $_SERVER)) {
        $ifModifiedSince = strtotime(preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]));
        if ($ifModifiedSince >= $lastModified) {
            header("HTTP/1.1 304 Not Modified");
            exit;
        }
    }
    
    $etag = $image->getEtag($size);
    if ($etag && array_key_exists("HTTP_IF_NONE_MATCH", $_SERVER)) {
        $requestEtag = str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH']));
        if ($requestEtag == $etag) {
            header("HTTP/1.1 304 Not Modified");
            exit;
        }
    }
    
    $cacheFile = $image->getPath($size);
    
} else {

    $img = new Imagick($image->getPath());
    if ($format === 'jpeg') {
        $img->enhanceImage();
    }
    $d = $img->getImageGeometry(); 
    $img->setImageFormat($format);
    
    switch ($_GET['size']) {
        case "tiny":
            $img->cropThumbnailImage(50, 50);
            break;
        case "thumb":
            $img->thumbnailImage(250, 250, true);
            break;
    }
    
    $cacheFile = $image->getPath($size);
    $image->createDirectory($size);
    $img->writeImage($cacheFile);
    
    $etag = $image->getEtag($size);
    $lastModified = time();
    
}

$filesize = $image->getFileLength($size);
header("Content-Length:{$filesize}");

$expires = 300;
if ($expires > 0) {
    header("Pragma: ");
    header("Cache-Control: public,age=$expires");
    header("Expires: " . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
} else {
    header("Pragma: no-cache");
    header("Cache-Control: private,no-cache,age=0");
}

if (!empty($etag)) {
    header("Etag: " . $etag);
}

if (!empty($lastModified)) {
    header("Last-Modified: " . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
}

readfile($cacheFile);
exit;