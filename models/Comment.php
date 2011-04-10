<?php

require_once 'StandardDocument.php';

class Comment extends StandardDocument
{
    protected static $_db = 'topics';
    protected static $_collection = 'comment';
    
    protected static $_requirements = array(
        'message' => 'Required',
        'created' => array('Required', 'Validator:MongoDate'),
        'modified' => array('Validator:MongoDate'),
        'user' => array('Document:User', 'Required', 'AsReference'),
        'post' => array('Document:Post', 'Required', 'AsReference'),
    );
    
    public function getMessage()
    {
        $message = $this->message; 
        if (preg_match('/(http:\/\/[^\(\)\[\]\s"\']+)/', $message, $m)) {
           $url = $m[1];
           $message = str_replace($url, "<a href=\"$url\">$url</a>", $message);
        }
        return $message;
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