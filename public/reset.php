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
    
    /*
    $userRef = MongoDBRef::create(User::getCollectionName(), $user->getId());
    $query = array('user' => $userRef);
    */
    
    $start = microtime(true);
    $total = $user->getComments()->count();
    echo "Total comments: $total (" . runtime($start) .  "ms)\n";  
    if ($total > 0) {
        $start = microtime(true);
        $deleted = $user->deleteComments(1000);
        echo "Deleted $deleted comments (" . runtime($start) .  "ms)\n"; 
        /*
        $comments = Comment::all($query)->limit(1000);
        foreach ($comments as $comment) {
            $start = microtime(true);
            $comment->delete();
            echo 'comment:' . $comment->getId() . " (" . runtime($start) .  "ms)\n";               
        }
        */
    }   
    
    $start = microtime(true);
    $total = $user->getPosts()->count();
    echo "Total posts: $total (" . runtime($start) .  "ms)\n";  
    if ($total > 0) {
        $start = microtime(true);
        $deleted = $user->deletePosts(1000);
        echo "Deleted $deleted posts (" . runtime($start) .  "ms)\n";
        /*
        $posts = Post::all($query)->limit(1000);
        foreach ($posts as $post) {
            $start = microtime(true);
            $post->delete();
            echo 'post:' . $post->getId() . " (" . runtime($start) .  "ms)\n";    
        }
        */
    }
    
    $start = microtime(true);
    $total = $user->getTopics()->count();
    echo "Total topics: $total (" . runtime($start) .  "ms)\n";  
    if ($total > 0) {
        $start = microtime(true);
        $deleted = $user->deleteTopics(1000);
        echo "Deleted $deleted topics (" . runtime($start) .  "ms)\n";
        /*
        $topics = Topic::all($query)->limit(1000);
        foreach ($topics as $topic) {
            $start = microtime(true);
            $topic->delete();
            echo 'topic:' . $topic->getId() . " (" . runtime($start) .  "ms)\n";    
        }
        */
    }
    
    $user->updateTotalTopics();
    $user->updateTotalPosts();
    $user->updateTotalComments();        
    
    print "runtime: " . runtime() . " ms\n";
    
}

exit;