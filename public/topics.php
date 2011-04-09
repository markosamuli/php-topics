<?php

header('Content-type: text/html; charset=utf-8');
require "../init.php";

if (empty($_SESSION['user'])) {
    header("Location:login.php");
    exit;
}

$query = array();

function getMongoDateFromString($string)
{
    if (preg_match("/^\d+(,\d+)?$/", $string)) {
        if (strpos($string, ",") === false) {
            $sec = (int)($string / 1000);
            $msec = (int)substr($string, -3);
            $usec = $msec * 1000;
        } else {
            list($sec, $usec) = explode(",", $string);
        }
        return new MongoDate($sec, $usec);
    }
}

$showMore = true;
$limit = 10;

if (isset($_GET['start']) && $date = getMongoDateFromString($_GET['start'])) {
    if (!isset($query['modified'])) {
        $query['modified'] = array();
    }
    $query['modified']['$lt'] = $date;
}

if (isset($_GET['end']) && $date = getMongoDateFromString($_GET['end'])) {
    if (!isset($query['modified'])) {
        $query['modified'] = array();
    }
    $query['modified']['$gt'] = $date;
    $limit = 100;
    $showMore = false;
}

// $totalTopics = Topic::all($query)->count();
$topics = Topic::all($query)
    ->sort(array("modified" => -1));
if ($limit > 0) {
    $topics->limit($limit);
}

$i = 0;
ob_start();
foreach ($topics as $topic) {
    $i++;
    $lastModified = $topic->modified;
    ?>
    <div class="topic" id="<?php echo $topic->getId(); ?>">
        <a class="title" href="topic.php?id=<?php echo $topic->getId(); ?>"><?php echo $topic->title; ?></a>
        <div class="info">
            <?php if ($topic->modified->sec > $topic->created->sec) { ?>
            <span class="modified">Updated: <?php echo date('j.n.Y H:i:s', $topic->modified->sec); ?></span>
            <?php } else { ?>
                <span class="created">Created: <?php echo date('j.n.Y H:i:s', $topic->created->sec); ?></span>            
                <?php } ?>                
            <?php if ($topic->totalPosts > 1) { ?>
                <span class="counter"><?php echo sprintf("%d posts", $topic->totalPosts); ?></span>
            <?php } elseif ($topic->totalPosts == 1) { ?>
                <span class="counter">one post</span>
            <?php } ?>     
        </div>
        <?php 
        if ($topic->totalPosts > 0) { ?>
            <div class="posts">
                <?php
                $posts = $topic->getPosts()->limit(1);
                foreach ($posts as $post) { ?>
                    <div class="post">
                    <p><?php echo $post->message; ?></p>
                    <div class="footer">
                        Posted <span class="created"><?php echo date('j.n.Y H:i:s', $post->created->sec); ?></span> 
                        by <span class="author"><?php echo $post->user->name; ?></span>
                    </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
<?php 
}
if ($showMore && $lastModified) {
?>
    <div class="pagination">
    <a id="more" href="topics.php?start=<?php echo "{$lastModified->sec},{$lastModified->usec}"; ?>">Show more topics</a>
    </div>
<?php
}
$topicsHtml = ob_get_clean();
$topicsHtml = trim($topicsHtml);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    echo $topicsHtml;
    exit;
}

?>
<html>
<head>
<title></title>
<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script type="text/javascript">

(function(){
    var serverTime = new Date(<?php echo time(); ?> * 1000),
        d = new Date();
    window.timeCorrection = serverTime.getTime() - d.getTime();
    window.lastUpdated = d.getTime() + window.timeCorrection;    
})();

$(document).ready(function () {
    
    var topics,
        updater,
        refresh = function () {
            if (updater) {
                window.clearTimeout(updater);
            }
            $.get(
                'topics.php',
                { end : window.lastUpdated },
                function (html, status, xhr) {
                    var d = new Date(), newHtml, newTopics, timeout;
                    timeout = 4000 + Math.floor(Math.random() * 1000);
                    if (html) {
                       newHtml = $('<div id="refresh-' + window.lastUpdated + '">' + html + '</div>');
                       newHtml.find('.topic').each(function(){
                           var id = $(this).attr('id');
                           if ($.inArray(id, topics)) {
                               $('#topics #' + id).remove();
                           } else {
                               topics.push(id);
                           }
                       });
                       $('#topics').prepend(newHtml);
                       window.lastUpdated = d.getTime() + window.timeCorrection;    
                       updater = window.setTimeout(refresh, 5000);                   
                   } else {
                       updater = window.setTimeout(refresh, 5000);
                   }
                }
            );
        },
        loadMore = function (event) {
           event.preventDefault();
           var link = $(this);
           link.parent().hide();
           $.get(
               link.attr('href'),
               function (html, status, xhr) {
                   link.parent().remove();
                   var newHtml;
                   if (html) {
                       newHtml = $(html);
                       newHtml.find('.topic').filter(function (index) {
                           return (!$.inArray($(this).attr('id'), topics));
                       }).remove();
                       $('#topics').append(newHtml);                   
                   }
               }
            ); 
        };
        
    topics =  $('.topic').map(function (index, domElement) { 
        return $(this).attr('id'); 
    }).get();
        
    $('#more').live('click', loadMore);
    
    updater = window.setTimeout(refresh, 5000);
});
</script>
</head>
<body>  
    <?php include("../include/header.php"); ?>
    <div class="topics">
    <?php include("topic_form.php"); ?>
    <div id="topics">
    <?php
    echo $topicsHtml;
    ?>
    </div>
    <?php if (isset($lastModified)) { ?>
    <?php
    }
    /*
    $last = $skip + $i;
    if ($last < $totalTopics) {
        $next = $skip + $limit;
    } else {
        $next = null;
    }
    if ($skip > 0) {
        $prev = max($skip - $limit, 0);
    } else {
        $prev = null;
    }
    ob_start();
    ?>
    <div class="pagination">
    <?php if ($prev !== null) { ?>
        <a class="prev" href="topics.php?start=<?php echo $prev; ?>">prev page</a>
    <?php } else { ?>    
         <span class="prev">prev page</span>
    <?php } ?>
    <?php if ($next !== null) { ?>
        <a class="next" href="topics.php?start=<?php echo $next; ?>">next page</a>
        <?php } else { ?>    
             <span class="next">next page</span>
        <?php } ?>
    </div>
    <?php
    $pagingHtml = ob_get_clean();
    */   
    ?>
  </div>
</body>
</html>