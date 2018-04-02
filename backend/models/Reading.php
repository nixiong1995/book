<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Reading extends ActiveRecord{

    //关联查询书
    public function getBook(){
        return $this->hasOne(Book::className(),['id'=>'book_id']);
    }

    //关联查询用户
    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

    public function getDetail(){
        return $this->hasOne(UserDetails::className(),['user_id'=>'user_id']);
    }
}