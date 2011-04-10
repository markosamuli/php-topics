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
        $user = User::one(array('secret' => $_POST['user']));
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
    
    $total = Comment::all($query)->count();
    $user->totalComments = $total;
    $user->save();
    if ($total > 0) {
        echo "Total comments: $total\n";
        $comments = Comment::all($query)->limit(1000);
        foreach ($comments as $comment) {
            $start = microtime(true);
            $comment->delete();
            echo 'comment:' . $comment->getId() . " (" . runtime($start) .  "ms)\n";               
        }
    }   
    
    $total = Post::all($query)->count();
    $user->totalPosts = $total;
    $user->save();
    if ($total > 0) {
        echo "Total posts: $total\n";
        $posts = Post::all($query)->limit(1000);
        foreach ($posts as $post) {
            $start = microtime(true);
            $post->delete();
            echo 'post:' . $post->getId() . " (" . runtime($start) .  "ms)\n";    
        }
    }
    
    $total = Topic::all($query)->count();
    $user->totalTopics = $total;
    $user->save();
    if ($total > 0) {
        echo "Total topics: $total\n";
        $topics = Topic::all($query)->limit(1000);
        foreach ($topics as $topic) {
            $start = microtime(true);
            $topic->delete();
            echo 'topic:' . $topic->getId() . " (" . runtime($start) .  "ms)\n";    
        }
    }
    
    print "runtime: " . runtime() . " ms\n";
    
}

exit;