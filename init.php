<?php

$startTime = microtime(true);

function runtime($start = null)
{
    global $startTime;
    if ($start === null) {
        $start = $startTime;
    }
    return ceil((microtime(true) - $start) * 1000);
}

date_default_timezone_set('Europe/Helsinki');
mb_internal_encoding("UTF-8");

if (!defined('NO_SESSION_SUPPORT')) {
    session_start();
}

define('ROOT_PATH', realpath(dirname(__FILE__)));
define('LIBRARY_PATH', ROOT_PATH . "/library");
define('TEMPLATE_PATH', ROOT_PATH . "/include");
define('UPLOAD_PATH', ROOT_PATH . "/uploads");

$paths = array();
// $paths[] = '/srv/lib/ZendFramework-1.11.5-minimal/library';
// $paths[] = ROOT_PATH . '/external/Shanty-Mongo/library';
$paths[] = LIBRARY_PATH;
$paths[] = TEMPLATE_PATH;
set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $paths));

require_once "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Shanty_Mongo_');

require_once "documents.php";

if (!defined('NO_SESSION_SUPPORT')) {
    if (isset($_SESSION['user'])) {
        if (!($_SESSION['user'] instanceof MongoID)) {
            $_SESSION['user'] = null;
        }
    }
}

function setProfilingLevel($level)
{
    // User::getMongoDb()->setProfilingLevel($level);
    // Topic::getMongoDb()->setProfilingLevel($level);
    // Post::getMongoDb()->setProfilingLevel($level);    
    // Comment::getMongoDb()->setProfilingLevel($level);    
    $result = MongoProfiler::setProfilingLevel($level);
    return $result;
}

if (isset($_GET['profile'])) {
    if ($_GET['profile'] == 'slow') {
        $result = setProfilingLevel(1);
        $_SESSION['profile'] = $_GET['profile'];
        $_SESSION['profile_debug'] = $result;
    } elseif ($_GET['profile'] == 'all') {
        $result = setProfilingLevel(2);
        $_SESSION['profile'] = $_GET['profile'];
        $_SESSION['profile_debug'] = $result;
    } else {
        $result = setProfilingLevel(0);
        $_SESSION['profile'] = false;
        $_SESSION['profile_debug'] = $result;
    }
}