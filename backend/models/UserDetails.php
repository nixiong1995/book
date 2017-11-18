<?php
namespace backend\models;
use yii\db\ActiveRecord;

class UserDetails extends ActiveRecord{
    public $file;
    //获取用户信息
    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

}