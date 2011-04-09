<?php

header('Content-type: text/html; charset=utf-8');
include("init.php");

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

/*
include("mongo.php");
$messageId =  new MongoId($_GET['id']);

$messages = $db->messages; // messages collection
$messageQuery = array('_id' => $messageId);
$message = $messages->findone($messageQuery);
*/
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
</body>
</html>