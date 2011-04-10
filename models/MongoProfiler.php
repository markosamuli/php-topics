<?php

require_once 'Shanty/Mongo.php';
require_once 'Shanty/Mongo/Collection.php';

class MongoProfiler extends Shanty_Mongo_Collection 
{
    protected static $_db = 'topics';
    
    /**
     * @return MongoCursor
     */ 
    public static function getProfilingData()
    {
        // $dbName = static::getDbName();
        // return self::getProfileCollection()->find(array("info" => "/{$dbName}.topic/" ));
        return self::getProfileCollection()->find()
            ->sort(array('$natural' => -1));
    }
    
    /**
     * @return MongoCollection
     */ 
    protected static function getProfileCollection()
    {
        return self::getMongoDb()->selectCollection('system.profile');
    }
    
    /**
     * @param integer $level 
     * @return array
     */
    public static function setProfilingLevel($level)
    {
        return self::getMongoDb()->command(array('profile' => $level));
    }
    
    /**
     * @return integer
     */ 
    public static function getProfilingLevel()
    {
        return self::getMongoDb()->getProfilingLevel();
    }
    
}



