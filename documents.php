<?php

require_once 'Shanty/Mongo.php';
require_once 'Shanty/Mongo/Collection.php';
require_once 'Shanty/Mongo/Document.php';

class MongoProfiler extends Shanty_Mongo_Collection 
{
    protected static $_db = 'topics';
    
    public static function getProfilingData()
    {
        // $dbName = static::getDbName();
        // return self::getProfileCollection()->find(array("info" => "/{$dbName}.topic/" ));
        return self::getProfileCollection()->find()
            ->sort(array('$natural' => -1));
    }
    
    protected static function getProfileCollection()
    {
        return self::getMongoDb()->selectCollection('system.profile');
    }
    
    public static function setProfilingLevel($level)
    {
        return self::getMongoDb()->command(array('profile' => $level));
    }
    
    public static function getProfilingLevel()
    {
        return self::getMongoDb()->getProfilingLevel();
    }
    
}

class User extends Shanty_Mongo_Document
{
    protected static $_db = 'topics';
    protected static $_collection = 'user';
    
    protected static $_requirements = array(
        'name' => 'Required',
        'email' => array('Required', 'Validator:EmailAddress'),
        'totalPosts' => array(),    
        'totalComments' => array(),
        'totalTopics' => array(),    
        'created' => array('Validator:MongoDate'),
        'modified' => array('Validator:MongoDate'),
        'secret' => array(),
    );
    
    public function getCreatedDate()
    {
        if ($this->created) {
            return new Zend_Date($this->created->sec);
        }
    }
    
    public function getModifiedDate()
    {
        if ($this->modified) {
            return new Zend_Date($this->modified->sec);
        }
    }
    
    public function preInsert()
    {
        $this->created = new MongoDate();
        $this->modified = new MongoDate();
    }
    
    public function preUpdate() 
    {
        $this->modified = new MongoDate();
    }
    
}

class Topic extends Shanty_Mongo_Document
{
    protected static $_db = 'topics';
    protected static $_collection = 'topic';
    
    protected static $_requirements = array(
        'title' => 'Required',
        'created' => array('Required', 'Validator:MongoDate'),
        'modified' => array('Validator:MongoDate'),               
        'user' => array('Document:User', 'Required', 'AsReference'),
        'users' => 'DocumentSet',
        'users.$' => array('Document:User', 'AsReference'),
        'posts' => 'DocumentSet',
        'posts.$' => array('Document:Post', 'AsReference'),
        'totalPosts' => array(),
    );
    
    public function getCreatedDate()
    {
        if ($this->created) {
            return new Zend_Date($this->created->sec);
        }
    }
    
    public function getModifiedDate()
    {
        if ($this->modified) {
            return new Zend_Date($this->modified->sec);
        }
    }
    
    public function init()
    {
        if ($this->created && $this->modified === null) {
            $this->modified = $this->created;
        }
    }
    
    public function preInsert()
    {
        $this->created = new MongoDate();
        $this->modified = new MongoDate();
    }
    
    public function preUpdate() 
    {
        $this->modified = new MongoDate();
    }
    
    public function postInsert()
    {
        $this->user->inc('totalTopics', 1);
        $this->user->save();
        
        $this->users->addDocument($this->user);
        $this->users->save();
    }
    
    public function postDelete()
    {
        $this->deletePosts();
    }
    
    public function deletePosts()
    {
        if ($this->user) {
            $this->user->inc('totalPosts', -1);     
            $this->user->save();
        }
        
        $posts = $this->getPosts();
        foreach ($posts as $post) {
            $post->delete();
        }
    }
    
    /**
     * @return array
     */ 
    public function getReferenceQuery()
    {
        $ref = MongoDBRef::create(self::getCollectionName(), $this->getId());
        return array('topic' => $ref);
    }
 
    public function getPosts()
    {
        $posts = Post::all($this->getReferenceQuery())
            ->sort(array('created' => -1));
        return $posts;
    }
    
}

class Post extends Shanty_Mongo_Document
{
    protected static $_db = 'topics';
    protected static $_collection = 'post';
    
    protected static $_requirements = array(
        'message' => 'Required',
        'created' => array('Required', 'Validator:MongoDate'),       
        'modified' => array('Validator:MongoDate'),               
        'user' => array('Document:User', 'Required', 'AsReference'),
        'topic' => array('Document:Topic', 'Required', 'AsReference'),  
        'comments' => 'DocumentSet',
        'comments.$' => array('Document:Comment', 'AsReference'),  
        'totalComments' => array(),    
    );   
    
    public function getCreatedDate()
    {
        if ($this->created) {
            return new Zend_Date($this->created->sec);
        }
    }
    
    public function getModifiedDate()
    {
        if ($this->modified) {
            return new Zend_Date($this->modified->sec);
        }
    }
    
    public function init()
    {
        if ($this->created && $this->modified === null) {
            $this->modified = $this->created;
        }
    } 
    
    public function preInsert()
    {
        $this->created = new MongoDate();
        $this->modified = new MongoDate();
    }
    
    public function preUpdate() 
    {
        $this->modified = new MongoDate();
    }
    
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
    
    public function deleteComments()
    {
        $comments = $this->getComments();
        foreach ($comments as $comment) {
            $comment->delete();
        }
    }
    
    public function getComments()
    {
        $comments = Comment::all($this->getReferenceQuery())
            ->sort(array('created' => 1));
        return $comments;
    }
    
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
                return;
            }
        }
    }
    
}

class Comment extends Shanty_Mongo_Document
{
    protected static $_db = 'topics';
    protected static $_collection = 'comment';
    
    protected static $_requirements = array(
        'message' => 'Required',
        'created' => array('Required', 'Validator:MongoDate'),
        'user' => array('Document:User', 'Required', 'AsReference'),
        'post' => array('Document:Post', 'Required', 'AsReference'),
    );
    
    public function getCreatedDate()
    {
        if ($this->created) {
            return new Zend_Date($this->created->sec);
        }
    }
    
    public function init()
    {
        if ($this->created && $this->modified === null) {
            $this->modified = $this->created;
        }
    }
    
    public function preInsert()
    {
        $this->created = new MongoDate();
        $this->modified = new MongoDate();
    }
    
    public function preUpdate() 
    {
        $this->modified = new MongoDate();
    }
    
    public function postDelete()
    {
        if ($this->post) {
            $this->post->inc('totalComments', -1);
            $this->post->save();
        }
        if ($this->user) {
            $this->user->inc('totalComments', -1);     
            $this->user->save();
        }
    }
    
    public function postInsert()
    {
        $this->post->comments->addDocument($this);
        $this->post->comments->save();
          
        $this->post->topic->modified = new MongoDate();
        $this->post->topic->save();
        
        $this->post->modified = new MongoDate();   
        $this->post->inc('totalComments', 1);
        $this->post->save();
        
        $this->user->inc('totalComments', 1);     
        $this->user->save();
    }
}