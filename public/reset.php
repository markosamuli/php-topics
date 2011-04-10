<?php

header("Content-Type:text/plain");
require "../init.php";

if (!empty($_SESSION['user'])) {
    
    $user = User::find($_SESSION['user']);
    
    $userRef = MongoDBRef::create(User::getCollectionName(), $user->getId());
    $query = array('user' => $userRef);
    
    $comments = Comment::all($query);
    foreach ($comments as $comment) {
        $comment->delete();
        echo 'comment:' . $comment->getId() . "\n";               
    }
    
    $posts = Post::all($query);
    foreach ($posts as $post) {
        $post->delete();
        echo 'post:' . $post->getId() . "\n";
    }
    
    $topics = Topic::all($query);
    foreach ($topics as $topic) {
        $topic->delete();
        echo 'topic:' . $topic->getId() . "\n";
    }
    
    $user->totalComments = 0;
    $user->totalPosts = 0;
    $user->totalTopics = 0;
    $user->save();
}

exit;