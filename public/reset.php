<?php

require "../init.php";

if (!empty($_SESSION['user'])) {
    $user = User::find($_SESSION['user']);
    
    $userRef = MongoDBRef::create(User::getCollectionName(), $user->getId());
    $query = array('user' => $userRef);

    $comments = Comment::remove($query);   
    $posts = Post::remove($query);
    $topics = Topic::remove($query);
}

header("Location:index.php");
exit;