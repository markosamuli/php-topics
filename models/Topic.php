<?php

require_once 'Shanty/Mongo.php';
require_once 'Shanty/Mongo/Collection.php';
require_once 'Shanty/Mongo/Document.php';

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
    
    public function init()
    {
        if ($this->created && $this->modified === null) {
            $this->modified = $this->created;
        }
    }
    
    /**
     * @return Zend_Date|null
     */
    public function getCreatedDate()
    {
        if ($this->created) {
            return new Zend_Date($this->created->sec);
        }
    }
    
    /**
     * @return Zend_Date|null
     */ 
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
            $this->user->inc('totalTopics', -1);     
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
 
    /**
     * @return MongoCursor
     */
    public function getPosts()
    {
        $posts = Post::all($this->getReferenceQuery())
            ->sort(array('created' => -1));
        return $posts;
    }
    
}