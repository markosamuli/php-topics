<?php

session_start();

$paths = array();
$paths[] = '/srv/lib/ZendFramework-1.11.5-minimal/library';
$paths[] = realpath(dirname(__FILE__)) . '/external/Shanty-Mongo/library';
set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $paths));

// require_once "Zend.php";
require_once "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Shanty_Mongo_');

require_once "documents.php";

if (isset($_SESSION['user'])) {
    if (!($_SESSION['user'] instanceof MongoID)) {
        $_SESSION['user'] = null;
    }
}