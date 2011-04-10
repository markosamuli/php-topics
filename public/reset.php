<?php

header("Content-Type:text/plain");
require "../init.php";

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
}

if (!empty($user)) {
    
    $userRef = MongoDBRef::create(User::getCollectionName(), $user->getId());
    $query = array('user' => $userRef);
    
    $comments = Comment::all($query)->limit(100);
    foreach ($comments as $comment) {
        $start = microtime(true);
        $comment->delete();
        echo 'comment:' . $comment->getId() . " (" . runtime($start) .  "ms)\n";               
    }
    
    $posts = Post::all($query)->limit(100);
    foreach ($posts as $post) {
        $start = microtime(true);
        $post->delete();
        echo 'post:' . $post->getId() . " (" . runtime($start) .  "ms)\n";    
    }
    
    $topics = Topic::all($query)->limit(100);
    foreach ($topics as $topic) {
        $start = microtime(true);
        $topic->delete();
        echo 'topic:' . $topic->getId() . " (" . runtime($start) .  "ms)\n";    
    }
    
    $user->totalComments = 0;
    $user->totalPosts = 0;
    $user->totalTopics = 0;
    $user->save();
    
    print "runtime: " . runtime() . " ms\n";
    
}

exit;