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

    public static function getList($idList){
        $list = [];
        foreach($idList as $id){
            $doc = self::getCollection()->findOne(['_id' => $id]);
            if (!empty($doc))
                $list[] = $doc;
        }
        return $list;
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

    public static function getById($id){
        return self::getCollection()->findOne(['_id' => $id]);
    }

    public static function deleteById($id){
        return self::getCollection()->deleteOne(['_id' => $id]);
    }

    public static function addLike($id, $userId){
        return self::getCollection()->updateOne(['_id'=>$id], ['$addToSet'=> ['likes' => $userId]]);
    }

    public static function toJson($doc, $visitor_id){
        $admins = ['ouxm0wUc3xGZSVsy3g2mQEF-hmW4', 'ouxm0waR9LPympnpEUDjPClG4XMI'];
        $likes = empty($doc['likes']) ? [] : $doc['likes']->getArrayCopy();
        return [
            '_id'     => $doc['_id']->__toString(),
            'screen_id' => $doc['screen_id'],
            'content' => $doc['content'],
            'created_at'=>date('H:i', $doc['_id']->getTimestamp()),
            'author'  => $doc['author']->getArrayCopy(),
            'likers'  => User::getList($likes),
            'likes'   => count($likes),
            'liked'   => in_array($visitor_id, $likes),
            'deletable'=> $doc['author']['_id'] == $visitor_id || in_array($visitor_id, $admins),
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

    public static function addMember($id, $memberId){
        return self::getCollection()->updateOne(['_id'=>$id], ['$addToSet'=> ['members' => $memberId]]);
    }

    public static function getById($id){
        return self::getCollection()->findOne(['_id' => $id]);
    }
}

