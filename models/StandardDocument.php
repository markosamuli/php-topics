<?php

require_once 'Shanty/Mongo.php';
require_once 'Shanty/Mongo/Collection.php';
require_once 'Shanty/Mongo/Document.php';

abstract class StandardDocument extends Shanty_Mongo_Document
{
    
    protected static $_requirements = array(
        'created' => array('Validator:MongoDate'),       
        'modified' => array('Validator:MongoDate'),
    );
    
    /*
    public function init()
    {
        if ($this->created && $this->modified === null) {
            $this->modified = $this->created;
        }
    }
    */
    
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
}

