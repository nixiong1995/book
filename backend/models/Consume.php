<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Consume extends ActiveRecord{

    //关联查询用户表
    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

    //关联查询书表
    public function getBook(){
        return $this->hasOne(Book::className(),['id'=>'book_id']);
    }

}