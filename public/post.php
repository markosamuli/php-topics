<?php

header('Content-type: text/html; charset=utf-8');
require "../init.php";

if (empty($_SESSION['user'])) {
    header("Location:login.php");
    exit;
}

if (!isset($_GET['id'])) {
  header("Location: topics.php");
  exit;
}

$post = Post::find($_GET['id']);
if ($post === null) {
    header("Location: topics.php");
      exit;
}

// $post->updateTotalComments();
?>
<html>
<head>
<title>Show comments for a post</title>
<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
</head>
<body>
    <?php include("header.php"); ?>
    <div class="header">
    <h2><a href="topic.php?id=<?php echo $post->topic->getId(); ?>"><?php echo $post->message; ?></a></h2>
       <div class="created"><?php echo date("j.n.Y H:i:s", $post->created->sec); ?></div>
    </div>
    <div class="comments">
    <?php 
    $comments = $post->getComments();
    if ($comments) { ?>
       <?php foreach ($comments as $comment) { ?>
          <div class="comment">
            <p><?php echo $comment->message; ?></p>
               <div class="created"><?php echo date("j.n.Y H:i:s", $comment->created->sec); ?></div>
          </div>
       <?php } ?>
    <?php } ?>
    <?php 
    include("comment_form.php"); 
    ?>
    </div>
<div class="footer"><a href="xml.php?post=<?php echo $post->getId(); ?>">XML</a></div>      
</body>
</html>