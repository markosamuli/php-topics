<?php
header('Content-type: text/xml; charset=utf-8');
require "../init.php";

if (isset($_GET['user'])) {
    $user = User::one(array('secret' => $_GET['user']));
} elseif (!empty($_SESSION['user'])) {
    $user = User::find($_SESSION['user']);
    if ($user->secret === null) {
        $user->secret = md5($_SESSION['user']);
        $user->save();
    }
    $params = array('user' => $user->secret);
    if (isset($_GET['topic'])) {
        $params['topic'] = $_GET['topic'];
    }
    if (isset($_GET['post'])) {
        $params['post'] = $_GET['post'];
    }
    $url = "xml.php?". http_build_query($params);
    header("Location:{$url}");
    exit;
} else {
    $user = null;
}

if ($user === null) {
    header("HTTP/1.1 404 Not Found");
}

function addPostXml($parentXml, $post)
{
    $postXml = $parentXml->addChild('post');
    $postXml->addAttribute('id', $post->getId());       
    
    $userXml = addUserXml($postXml, $post->user);
    
    $postXml->addChild('message', $post->message);
    $postXml->addChild('created', $post->getCreatedDate()->toString(Zend_Date::RFC_3339));        
    $postXml->addChild('modified', $post->getModifiedDate()->toString(Zend_Date::RFC_3339));

    return $postXml;
    
}

function addCommentXml($parentXml, $comment)
{
    $commentXml = $parentXml->addChild('comment');
    $commentXml->addAttribute('id', $comment->getId());
    
    $userXml = addUserXml($commentXml, $comment->user);

    $commentXml->addChild('message', $comment->message);
    $commentXml->addChild('created', $comment->getCreatedDate()->toString(Zend_Date::RFC_3339));
    
    return $commentXml;
}

function addTopicXml($parentXml, $topic)
{
    $topicXml = $parentXml->addChild('topic');
    $topicXml->addAttribute('id', $topic->getId());  
    
    $userXml = addUserXml($topicXml, $topic->user);
    
    $topicXml->addChild('title', $topic->title);
    $topicXml->addChild('created', $topic->getCreatedDate()->toString(Zend_Date::RFC_3339));        
    $topicXml->addChild('modified', $topic->getModifiedDate()->toString(Zend_Date::RFC_3339));
    
    return $topicXml;
}

function addUserXml($parentXml, $user)
{
    $userXml = $parentXml->addChild('user');
    $userXml->addChild('name', $user->name);
    $userXml->addAttribute('id', $user->getId());
    
    return $userXml;
}

function addTopicsXml($parentXml, $topics)
{
    
    $topicsXml = $parentXml->addChild('topics');
    foreach ($topics as $topic) {
    
        $topicXml = addTopicXml($topicsXml, $topic);
    
        $postsXml = $topicXml->addChild('posts');
        $postsXml->addAttribute('total', (int)$topic->totalPosts);
    
        $posts = $topic->getPosts()->limit(10);
        foreach ($posts as $post) {
        
            $postXml = addPostXml($postsXml, $post);
        
            $commentsXml = $postXml->addChild('comments');
            $commentsXml->addAttribute('total', (int)$post->totalComments);
            foreach ($post->getComments() as $comment) {
                $commentXml = addCommentXml($commentsXml, $comment);
            }
        }
     
    }
    
}

$doc = <<<EOF
<?xml version="1.0" encoding="UTF-8" ?>
<data></data>
EOF;

$sxe = new SimpleXMLElement($doc);

$userXml = addUserXml($sxe, $user);
$userTopicsXml = $userXml->addChild('topics');
$userTopicsXml->addAttribute('total', $user->updateTotalTopics());
$userPostsXml = $userXml->addChild('posts');
$userPostsXml->addAttribute('total', $user->updateTotalPosts());
$userCommentsXml = $userXml->addChild('comments');
$userCommentsXml->addAttribute('total', $user->updateTotalComments());

if (isset($_GET['post'])) {
    
    $queryXml = $sxe->addChild('query');
    $paramXml = $queryXml->addChild('param');
    $paramXml->addAttribute('name', 'post');
    $paramXml->addAttribute('value', $_GET['post']);
    
    $post = Post::find($_GET['post']);
    if ($post) {
        $postXml = addPostXml($sxe, $post);
    } else {
        header("HTTP/1.1 404 Not Found");
    }
    
} elseif (isset($_GET['topic'])) {
    
    $queryXml = $sxe->addChild('query');
    $paramXml = $queryXml->addChild('param');
    $paramXml->addAttribute('name', 'topic');
    $paramXml->addAttribute('value', $_GET['topic']);

    $topic = Topic::find($_GET['topic']);
    if ($topic) {
        $topicXml = addTopicXml($sxe, $topic);
    } else {
        header("HTTP/1.1 404 Not Found");
    }

} else {
    
    if (isset($_GET['limit'])) {
        $limit = min($_GET['limit'], 1000);
    } else {
        $limit = 10;
    }
    
    $topics = Topic::all()
        ->sort(array("modified" => -1))
        ->limit($limit);
    $topicsXml = addTopicsXml($sxe, $topics);
    
}

$sxe->addChild('runtime', runtime() . " ms");
echo $sxe->asXML();