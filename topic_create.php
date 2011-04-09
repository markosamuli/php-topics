<?php

header('Content-type: text/html; charset=utf-8');
include("init.php");

if (isset($_GET['rest']) || isset($_POST['rest'])) {
    $restful = true;
} else {
    $restful = false;
}

if ($restful) {
    if (isset($_POST['user'])) {
        $user = User::find($_POST['user']);
    } else {
        $user = null;
    }
    if ($user === null) {
        header("HTTP/1.1 403 Forbidden");
        exit;
    }
} elseif (!empty($_SESSION['user'])) {
    $user = User::find($_SESSION['user']);
} else {
    header("Location:login.php");
    exit;
}

if (!empty($_POST['title'])) {
  $topic = new Topic();
  $topic->title = $_POST['title'];
  $topic->user = $user;
  if ($topic->save()) {
      $topicId = $topic->getId();
      if ($restful) {
          header("HTTP/1.1 201 Created");
      }
      header("Location: topic.php?id={$topicId}");
      exit;
  } else {
      if ($restful) {
          header("HTTP/1.1 500 Internal Server Error");
          exit;
      }
  }
} else {
    if ($restful) {
        header("HTTP/1.1 400 Bad Request");
        exit;
    }
}

header("Location: topics.php");
exit;