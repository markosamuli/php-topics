<?php

header('Content-type: text/html; charset=utf-8');
require "../init.php";

if (empty($_SESSION['user'])) {
    header("Location:login.php");
    exit;
}

// include("mongo.php");
// $topics = $db->topics;
?>
<html>
<head>
<title></title>
<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
</head>
<body>  
    <?php include("../include/header.php"); ?>
    <div class="topics">
    <?php include("topic_form.php"); ?>
    <?php
    /*
    $cursor = $topics->find()->limit(10);
    $cursor->sort(array("created" => -1));
    */
    // Post::drop();
    // Topic::drop();
    // Comment::drop();
    $topics = Topic::all()->sort(array("modified" => -1))->limit(10);    
    foreach ($topics as $topic) {
        /*
        if (isset($topic['total_messages'])) {
        $messages = $topic['total_messages'];
        } else {
        $messages = null;
        }
        */
        $messages = null;
        ?>
        <div class="topic">
            <a class="title" href="topic.php?id=<?php echo $topic->getId(); ?>"><?php echo $topic->title; ?></a>
            <span class="counter"><?php echo $messages; ?></span>
            <div class="info">
                <span class="created">Created: <?php echo date('j.n.Y H:i:s', $topic->created->sec); ?></span>
                <?php if ($topic->modified->sec > $topic->created->sec) { ?>
                | <span class="modified">Last update: <?php echo date('j.n.Y H:i:s', $topic->modified->sec); ?></span>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
  </div>
</body>
</html>