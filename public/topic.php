<?php

require "../init.php";

if (empty($_SESSION['user'])) {
    header("Location:login.php");
    exit;
}

// include("mongo.php");
// include("models.php");

header('Content-type: text/html; charset=utf-8');

if (!isset($_GET['id'])) {
    header("Location:topics.php");
    exit;
}
  
  /*
  Congo_Database::getInstance()->getDb();
  
  $topics = new Topics();
  $messages = new Messages();
  $topic = $topics->find($_GET['id']);
  */

//  $topics = $db->topics;  
//  $topicId =  new MongoId($_GET['id']);
//  $query = array('_id' => $topicId);
//  $topic = $topics->findone($query);

$topic = Topic::find($_GET['id']);
if ($topic === null) {
    header("Location:topics.php");
    exit;
}

?>
<html>
<head>
<title><?php echo $topic->title; ?></title>
<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
</head>
<body>
  <?php include("header.php"); ?>
    <div class="header">
    <h1><?php echo $topic->title; ?></h1>
    <a href="topics.php">&laquo; List all topics</a>
    </div>
    <div class="messages">
    
      <?php include('message_form.php'); ?>
            
      <?php
      $posts = $topic->getPosts();
      if ($posts) {
        /*
        $messageQuery = array('topic' => $topicId);
        $topicMessages = $messages->find(
            $messageQuery, 
            array('comments' => array('$slice' => -5)) // fetch only 5 last comments
        );
        // display latest messages first
        $topicMessages->sort(array('created' => -1));
        */
        
        
        
        /*
        $db->messageComments->drop();
        $results = countMessageComments($messageQuery);
        var_dump($results);
        $comments = $db->selectCollection($results['result'])->find();
        foreach ($comments as $c) {
          var_dump($c);
        }
        var_dump($comments);
        var_dump(iterator_to_array($comments));
//        $comments = $db->selectCollection($results)->find();
//        var_dump(iterator_to_array($comments));
        */
        ?>
        <?php foreach ($posts as $post) { ?>
          <div class="message" id="<?php echo $post->getId(); ?>">
            <div class="post">
            <p><?php echo $post->message; ?></p>
            <div class="footer">
            Posted 
            <span class="created"><?php echo date("j.n.Y H:i:s", $post->created->sec); ?></span>
            by <span class="author"><?php echo $post->user->name; ?></span>
            </div>
            </div>
            <div class="comments">
            <?php
            $limit = 5;
            $comments = $post->getComments()->limit($limit);
            if ($comments) { ?>
               <?php if ($post->totalComments > $limit) { ?>
                  <div class="more">
                  <a href="comments.php?id=<?php echo $post->getId(); ?>">Show all comments
                  (<?php echo $post->totalComments; ?>)
                  </a> 
                  </div>
               <?php } ?>
               <?php foreach ($comments as $comment) { ?>
                  <div class="comment">
                    <p><?php echo $comment->message; ?></p>
                    <div class="created"><?php echo date("j.n.Y H:i:s", $comment->created->sec); ?></div>
                  </div>
               <?php } ?>
            <?php 
            }
            // $messageId =  new MongoId($message['_id']);
            include("comment_form.php"); 
            ?>
            </div>
          </div>  
        <?php } ?>
      <?php } ?>
      
    </div>
</body>
</html>