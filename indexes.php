<?php

include "init.php";

Topic::deleteIndexes();
User::deleteIndexes();
Post::deleteIndexes();
Comment::deleteIndexes();

echo "Collection: " . Topic::getCollectionName() . "\n";  
$status = Topic::ensureIndex(array('modified'));
echo ($status ? "OK" : "FAILED") . "\n";

echo "Collection: " . User::getCollectionName() . "\n";  
User::ensureIndex(array('email' => 1), array('unique' => true));
echo ($status ? "OK" : "FAILED") . "\n";

echo "Collection: " . Post::getCollectionName() . "\n";  
Post::ensureIndex(array('topic', 'modified')); 
echo ($status ? "OK" : "FAILED") . "\n";

echo "Collection: " . Comment::getCollectionName() . "\n";  
Comment::ensureIndex(array('post', 'created'));
echo ($status ? "OK" : "FAILED") . "\n";