<?php

header('Content-type: text/html; charset=utf-8');
require "../init.php";

if (isset($_GET['test'])) {
    $_POST = $_GET;
}

if (isset($_POST['user'])) {
    $user = User::find($_POST['user']);
} elseif (!empty($_SESSION['user'])) {
    $user = User::find($_SESSION['user']);
} else {
    header("Location:login.php");
    exit;
}

header('Content-type: text/html; charset=utf-8');

if (!isset($_POST['id'])) {
  header("Location: topics.php");
  exit;
}

$topic = Topic::find($_POST['id']);
if ($topic === null) {
    header("Location: topics.php");
    exit;
}

/*
include("mongo.php");
$topicId =  new MongoId($_POST['id']);
*/

if (!empty($_POST['message'])) {

      /*
      $topics = $db->topics; // topics collection
      $topicQuery = array('_id' => $topicId);
      $topic = $topics->findone($topicQuery);
      */
        
    $post = new Post();
    $post->message = $_POST['message'];
    $post->topic = $topic;
    $post->user = $user;
    if ($post->save()) {
        /*
        $topic->posts->addDocument($post);
        $topic->posts->save();
        $topic->modified = new MongoDate();
        $topic->save();
        $topic->inc('totalPosts', 1);
        $user->inc('totalPosts', 1);
        */
    } else {
        throw new Exception("Failed to create new post");
    }
    
    // $messages->insert($message, true);
    // $messages->ensureIndex(array('topic'));
    
    // the message we just added
    // $messageId = $message['_id'];
    // $messageQuery = array('_id' => $messageId);
    // $message = $messages->findone($messageQuery);
    
    // create reference so we can add message to the topic
    // $refToMessage = $messages->createDBRef($message);
    
    // add message to the topic
    /*
    $topics->update(
      $topicQuery, 
      array('$push' => array('messages' => $refToMessage))
    );
    */
    // add message to the topic
    /*
    $topics->update(
      $topicQuery, 
      array('$inc' => array('total_messages' => 1))
    );
    */
}

$topicId = $topic->getId();
header("Location: topic.php?id={$topicId}");
exit;