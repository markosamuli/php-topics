<?php

require_once 'StandardDocument.php';

class Post extends StandardDocument
{
    protected static $_db = 'topics';
    protected static $_collection = 'post';
    
    protected static $_requirements = array(
        'message' => 'Required',
        'created' => array('Required'),            
        'user' => array('Document:User', 'Required', 'AsReference'),
        'topic' => array('Document:Topic', 'Required', 'AsReference'),  
        'comments' => 'DocumentSet',
        'comments.$' => array('Document:Comment', 'AsReference'),  
        'totalComments' => array(),    
    );
    
    public function postInsert()
    {
        $this->topic->posts->addDocument($this);
        $this->topic->posts->save();
        
        $this->topic->modified = new MongoDate();
        $this->topic->inc('totalPosts', 1);           
        $this->topic->save();
        
        $this->user->inc('totalPosts', 1);   
        $this->user->save();
    }
    
    public function preDelete()
    {
        // $this->deleteComments();
    }
    
    public function postDelete()
    {
        if ($this->topic) {
            $this->topic->inc('totalPosts', -1);
            $this->topic->save();
        }
        
        if ($this->user) {
            $this->user->inc('totalPosts', -1);     
            $this->user->save();
        }
        
        $this->deleteComments();
    }
    
    /**
     * @return array
     */ 
    public function getReferenceQuery()
    {
        $ref = MongoDBRef::create(self::getCollectionName(), $this->getId());
        return array('post' => $ref);
    }
    
    /**
     * @return integer
     */ 
    public function deleteComments()
    {
        $i = 0;
        $comments = $this->getComments();
        foreach ($comments as $comment) {
            $comment->delete();
            $i++;
        }
        return $i;
    }
    
    /**
     * @return MongoCursor
     */ 
    public function getComments()
    {
        $comments = Comment::all($this->getReferenceQuery())
            ->sort(array('created' => 1));
        return $comments;
    }
    
    /**
     * @return integer
     * @throws Exception
     */ 
    public function updateTotalComments()
    {
        $db = self::getMongoDb();
        
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
        
        $query = array('_id' => $this->getId());
        $out = $db->command(array(
            "mapreduce" => self::getCollectionName(), 
            "map" => $map,
            "reduce" => $reduce,
            "query" => $query,
            "out" => array("inline" => 1),
        ));
        
        foreach ($out['results'] as $row) {
            if ($row['_id'] == $this->getId()) {
                $comments = $row['value']['count'];
                $this->totalComments = $comments;
                $this->save();
                return $comments;
            }
        }
        
        throw new Exception("Failed to update total comments for the post");
        
    }
    
}