<?php

try {
   $mongo = new Mongo();
} catch(MongoConnectionException $e) {
   die('Could not connect. Check to make sure MongoDB is running.');
}

$db = $mongo->testdb;

function updateMessageComments($id)
{
   global $db;
   $messageQuery = array('_id' => $id);
   $messages = $db->messages;
   $result = countMessageComments($messageQuery);
   $comments = $db->selectCollection($result['result'])->findone($messageQuery);
   $messages->update(
      $messageQuery, 
      array(
        '$set' => array('total_comment_count' => $comments['value']['count'])
      )
   );
}

function getTotalMessageComments($id)
{
    global $db;
    $comments = $db->messageComments->findone(array('_id' => $id));
    if ($comments) {
        return $comments['value']['count'];
    }
}

function countMessageComments($messageQuery)
{
    global $db;
    $map = new MongoCode(
        "function () { " .
          "if (!this.comments) { return; } " .
            "for (index in this.comments) { " .
                "emit(this._id, {count:1}); " .
            "} " .
        "}"
    );
    $reduce = new MongoCode(
        "function (previous, current) { ".
          "var count = 0; " .
          "for (index in current) { ".
              "count += current[index]['count']; " . 
          "} " .
          "return {count:count}; " .
        "}"
    );
    $command = array(
      "mapreduce" => "message", 
      "map" => $map,
      "reduce" => $reduce,
      "out" => array("merge" => "messageComments")
    );
    if ($messageQuery) {
       $command["query"] = $messageQuery;
    }
    $results = $db->command($command);
    return $results;
}