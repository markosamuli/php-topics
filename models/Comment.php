<?php

require_once 'Shanty/Mongo.php';
require_once 'Shanty/Mongo/Collection.php';
require_once 'Shanty/Mongo/Document.php';

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