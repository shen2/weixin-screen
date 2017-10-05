<?php
namespace Models;

class User{
    protected static $db;
    protected static $collectionName = 'users';

    public static function getCollection(){
        if (empty(static::$db)){
            static::$db = new \MongoDB\Client();
        }
        return static::$db->wedding->users;
    }
    public static function save($doc){
        $result = self::getCollection()->replaceOne(['_id'=> $doc['_id']], $doc, ['upsert'=>true]);
    }

    public static function getById($id){
        return self::getCollection()->findOne(['_id' => $id]);
    }

    public static function getMulti($idList){
        $map = [];
        foreach($idList as $id){
            $map[$id] = self::getCollection()->findOne(['_id' => $id]);
        }
        return $map;
    }
}

class Post{
    protected static $db;
    protected static $collectionName = 'posts';

    public static function getCollection(){
        if (empty(static::$db)){
            static::$db = new \MongoDB\Client();
        }
        return static::$db->wedding->posts;
    }
    public static function insertOne($doc){
        $result = self::getCollection()->insertOne($doc);
        return $result->getInsertedId();
    }

    public static function toJson($doc){
        return [
            '_id'     => $doc['_id']->__toString(),
            'screen_id' => $doc['screen_id'],
            'content' => $doc['content'],
            'created_at'=>date('H:i', $doc['_id']->getTimestamp()),
            'author'  => $doc['author']->getArrayCopy(),
        ];
    }

    public static function getByScreenId($id){
        return self::getCollection()->find(['screen_id' => $id], ['sort' => ['_id'=> -1,], 'limit'=> 200,]);
    }
}

class Screen{
    protected static $db;
    protected static $collectionName = 'screens';

    public static function getCollection(){
        if (empty(static::$db)){
            static::$db = new \MongoDB\Client();
        }
        return static::$db->wedding->screens;
    }
    public static function save($doc){
        $result = self::getCollection()->replaceOne(['_id'=> $doc['_id']], $doc, ['upsert'=>true]);
    }

    public static function insertOne($doc){
        $result = self::getCollection()->insertOne($doc);
        return $result->getInsertedId();
    }

    public static function getById($id){
        return self::getCollection()->findOne(['_id' => $id]);
    }
}

