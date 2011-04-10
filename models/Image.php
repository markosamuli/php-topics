<?php

require_once 'Shanty/Mongo.php';
require_once 'Shanty/Mongo/Collection.php';
require_once 'Shanty/Mongo/Document.php';

class Image extends StandardDocument
{
    protected static $_db = 'topics';
    protected static $_collection = 'image';
    
    protected static $_requirements = array(
        'filename' => array('Required'),
        'fileExists' => array(),
        'originalFilename' => array(),
        'user' => array('Document:User', 'Required', 'AsReference'),
    );
    
    public function fileExists()
    {
        if ($this->fileExists === null) {
            $this->fileExists = file_exists($this->getPath());
            $this->save();
        }
        return $this->fileExists;
    }
    
    public function getFormat()
    {
        if ($this->fileExists()) {
            $info = pathinfo($this->getPath());
            switch ($info['extension']) {
                case "png":
                    $format = 'png';
                    break;
                case "gif":
                    $format = 'gif';
                    break;
                case "jpg":
                case "jpeg":
                    $format = 'jpeg';
                    break;
                default:
                    $format = null;
                    break;
            }
            return $format;
        }
    }
    
    public function cacheFileExists($size)
    {
        $path = $this->getPath($size);
        return file_exists($path);
    }
    
    public function getFileLength($size)
    {
        if ($this->cacheFileExists($size)) {
            $path = $this->getPath($size);
            return filesize($path);
        }
    }
    
    public function getEtag($size)
    {
        if ($this->cacheFileExists($size)) {
            $path = $this->getPath($size);
            $size = filesize($path);
            $time = filemtime($path);
            $etag = md5($size) . "-" . md5($time);
            return $etag;
        }
    }
    
    public function getLastModified($size)
    {
        if ($this->cacheFileExists($size)) {
            $path = $this->getPath($size);
            return filemtime($path);
        }
    }
    
    public function getUserPath()
    {
        return UPLOAD_PATH . "/" . $this->user->getId();
    }
    
    public function createDirectory($size)
    {
        $path = $this->getDirectory($size);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }
    
    public function getDirectory($size)
    {
        return $this->getUserPath() . "/". $size;
    }
    
    public function getPath($size = "original")
    {
        return $this->getDirectory($size) . "/". $this->filename;
    }
    
}