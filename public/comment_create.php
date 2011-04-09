<?php

require "../init.php";

if (isset($_POST['user'])) {
    $user = User::find($_POST['user']);
} elseif (!empty($_SESSION['user'])) {
    $user = User::find($_SESSION['user']);
} else {
    header("Location:login.php");
    exit;
}

if (!isset($_POST['id'])) {
  header("Location: topics.php");
  exit;
}

$post = Post::find($_POST['id']);
if ($post === null) {
    header("Location: topics.php");
    exit;
}

/*
include("mongo.php");
$messageId =  new MongoId($_POST['id']);

$messages = $db->messages; // messages collection
$messageQuery = array('_id' => $messageId);
$message = $messages->findone($messageQuery);

$messageId = $message['_id'];
$topicId = $message['topic'];
*/

if (!empty($_POST['comment'])) {
  
  $comment = new Comment();
  // $comment->created = new MongoDate;
  $comment->user = $user;
  $comment->post = $post;
  $comment->message = $_POST['comment'];
  if ($comment->save()) {
      /*
      $post->comments->addDocument($comment);
      $post->comments->save();
      $post->inc('totalComments', 1);
      $post->modified = new MongoDate();
      $post->save();
      */
      if (isset($_POST['return']) && $_POST['return'] == 'topic') {
          $topicId = $post->topic->getId();
          header("Location:topic.php?id={$topicId}");
          exit;
      } else {
          $postId = $post->getId();
          header("Location: comments.php?id={$postId}");
            exit;
        }
  } 
  
  /*
  $comment = array(
    'comment' => $_POST['comment'],
    'created' => new MongoTimestamp(),
  );
  if (isset($message['comments']) && !isset($message['total_comments'])) {
      $updateMessageComments = true;
  } else {
      $updateMessageComments = false;
  }
  $messages->update(
    $messageQuery, 
    array(
      '$push' => array('comments' => $comment)
    )
  );
  $messages->update(
    $messageQuery, 
    array(
      '$inc' => array('total_comments' => 1)
    )
  );
  countMessageComments($messageQuery);
  */
  /*
  if ($updateMessageComments) {
      $messageComments = $db->messageComments->findone($messageQuery);
      $messages->update(
        $messageQuery, 
        array(
          '$set' => array('total_comments' => $comments['value']['count'])
        )
      );
  } else {
      
  }
  $results = countMessageComments($messageQuery);
  $comments = $db->selectCollection($results['result'])->find($messageQuery);
  */
  
}

$topicId = $post->topic->getId();
header("Location: topic.php?id={$topicId}");
exit;
