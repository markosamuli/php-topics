<?php

class Congo_Database
{
   protected static $_instance;
   
   /**
    * @var Mongo
    */ 
   protected $_mongo;
   protected $_defaultDatabase;
   
   /**
    * @return Congo_Database
    */
   public static function getInstance()
   {
       if (self::$_instance === null) {
           self::$_instance = new Congo_Database();
       }
       return self::$_instance;
   }
   
   public function __construct()
   {
       $this->_defaultDatabase = 'testdb';
   }
   
   /**
    * @return Mongo
    * @throws MongoConnectionException
    */ 
   public function getMongo()
   {
       if ($this->_mongo === null) {
           $this->_mongo = new Mongo();
       }
       return $this->_mongo;
   }
   
   /**
    * @return MongoDB
    */ 
   public function getDb($database = null)
   {
       if ($database === null) {
           $database = $this->_defaultDatabase;
       }
       return $this->getMongo()->selectDB($database);
   }
   
}

abstract class Congo_Collection
{
    protected $_collection;
    
    public function __construct()
    {
        $this->_database = Congo_Database::getInstance();
    }
    
    public function getCollection()
    {
        $this->_database->getDb()->selectCollection($this->_collection);
    }
    
    public function get($id)
    {
        if (!($id instanceof MongoID)) {
            $id = new MongoID();
        }
        $query = array('_id' => $id);
        return $this->findone($query);
    }
    
    public function getQuery($query)
    {
        if (is_array($query)) {
            return $query;
        }
        if ($query instanceof MongoID) {
            return array('_id' => $query);
        } elseif (is_string($query)) {
            return array('_id' => new MongoID($query));
        }
    }
    /*
    public function getId($id)
    {
        if (!($id instanceof MongoID)) {
            $id = new MongoID();
        }
        return $id;
    }
    */
    public function find($query, $fields = null)
    {
        $query = $this->getQuery($query);
        return $this->getCollection()->find($query, $fields);
    }
    
    public function findone($query, $fields = null)
    {
        $query = $this->getQuery($query);        
        return $this->getCollection()->findone($query, $fields);
    }
    
}

class Topics extends Congo_Collection
{
    protected $_collection = 'topics';
}

class Messages extends Congo_Collection
{
    protected $_collection = 'messages';
}

abstract class Congo_Model
{

   protected $_collection;
   protected $_data;
   
   public function __construct($data = null)
   {
       if ($data) {
           $this->setData($data);
       }
   }
   
   public function __get($name)
   {
       if ($this->_data === null) {
           return null;
       }
       if (isset($this->_data[$name])) {
           return $this->_data[$name];
       }
       return parent::__get($name);
   }
   
   /**
    * @return array
    */
   public function getData()
   {
       return $this->_data;
   }
   
   public function setData(array $data)
   {
       $this->_data = $data;
       return $this;
   }
   
   /**
    * @return Congo_Database
    */ 
   protected function getAdapter()
   {
       if ($this->_adapter === null) {
           $this->_adapter = Congo_Database::getInstance();
       }
       return $this->_adapter;
   }
   
   /**
    * @return MongoDB
    */ 
   protected function getDb()
   {
       return $this->getAdapter()->getDb();
   }
   
   /**
    * @return MongoCollection
    */  
   protected function getCollection($collection = null)
   {
       if ($collection === null) {
           $collection = $this->_collection;
       }
       return $this->getDb()->selectCollection($collection);
   }
   
   protected function find($id)
   {
       if ($id)
       $query = array('_id' => new MongoID($id));
       return $this->getCollection()->findone($query);
   }
   
}