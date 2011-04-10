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

if (!empty($_POST['comment'])) {
  $comment = new Comment();
  $comment->user = $user;
  $comment->post = $post;
  $comment->message = strip_tags($_POST['comment']);
  if ($comment->save()) {
      $postId = $post->getId();
      if (isset($_POST['return']) && $_POST['return'] == 'topic') {
          $topicId = $post->topic->getId();
          header("Location:topic.php?id={$topicId}#{$postId}");
          exit;
      } else {
          header("Location:post.php?id={$postId}");
          exit;
      }
  } 
}

$postId = $post->getId();
$topicId = $post->topic->getId();
header("Location: topic.php?id={$topicId}#{$postId}");
exit;
