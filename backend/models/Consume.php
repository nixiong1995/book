<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Consume extends ActiveRecord{

    //关联查询用户表
    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

}