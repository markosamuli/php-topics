<?php

require_once 'Shanty/Mongo.php';
require_once 'Shanty/Mongo/Collection.php';
require_once 'Shanty/Mongo/Document.php';

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
    
    /**
     * @return array
     */ 
    public function getReferenceQuery()
    {
        $ref = MongoDBRef::create(self::getCollectionName(), $this->getId());
        return array('user' => $ref);
    }
    
    public function updateTotalTopics()
    {
        $total = $this->getTopics()->count();
        $this->totalTopics = $total;
        $this->save();
        return $total;
    }
    
    public function updateTotalPosts()
    {
        $total = $this->getPosts()->count();
        $this->totalPosts = $total;
        $this->save();
        return $total;
    }
    
    public function updateTotalComments()
    {
        $total = $this->getComments()->count();
        $this->totalComments = $total;
        $this->save();
        return $total;
    }
    
    public function getTopics()
    {
        return Topic::all($this->getReferenceQuery());
    }
    
    public function getPosts()
    {
        return Post::all($this->getReferenceQuery());
    }
    
    public function getComments()
    {
        return Comment::all($this->getReferenceQuery());
    }
    
    public function deleteTopics($limit = 100)
    {
        $i = 0;
        $topics = $this->getTopics()->limit($limit);
        foreach ($topics as $topic) {
            $topic->delete();
            $i++;
        }
        return $i;
    }
    
    public function deletePosts($limit = 100)
    {
        $i = 0;
        $posts = $this->getPosts()->limit($limit);
        foreach ($posts as $post) {
            $post->delete();
            $i++;
        }
        return $i;
    }
    
    public function deleteComments($limit = 100)
    {
        $i = 0;
        $comments = $this->getComments()->limit($limit);
        foreach ($comments as $comment) {
            $comment->delete();
            $i++;
        }
        return $i;
    }
    
}