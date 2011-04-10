<?php

include "init.php";

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

/*
Topic::deleteIndexes();
User::deleteIndexes();
Post::deleteIndexes();
Comment::deleteIndexes();
*/

try {
    echo "Collection: " . Topic::getCollectionName() . "\n";  
    $status = Topic::ensureIndex(array('modified'), array('safe' => 1));
    echo ($status ? "OK" : "FAILED") . "\n";
} catch (MongoException  $e) {
    echo "MongoException: " . $e->getMessage() . "\n";
}

try {
    echo "Collection: " . User::getCollectionName() . "\n";  
    User::ensureIndex(array('email' => 1), array('unique' => true, 'safe' => 1));
    echo ($status ? "OK" : "FAILED") . "\n";
} catch (MongoException  $e) {
    echo "MongoException: " . $e->getMessage() . "\n";
}

try {
    echo "Collection: " . Post::getCollectionName() . "\n";  
    Post::ensureIndex(array('modified'), array('safe' => 1)); 
    echo ($status ? "OK" : "FAILED") . "\n";
} catch (MongoException  $e) {
    echo "MongoException: " . $e->getMessage() . "\n";
}

/*
try {
    echo "Collection: " . Comment::getCollectionName() . "\n";  
    Comment::ensureIndex(array('created'), array('safe' => 1));
    echo ($status ? "OK" : "FAILED") . "\n";
} catch (MongoException  $e) {
    echo "MongoException: " . $e->getMessage() . "\n";
}
*/