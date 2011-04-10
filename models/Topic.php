<?php

require_once 'StandardDocument.php';

class Topic extends StandardDocument
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