<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Seckill extends ActiveRecord{

    public function rules()
    {
        return [
            [['begin_time','price','end_time'],'required'],
            ['people','integer']

        ];
    }

    public function attributeLabels()
    {
        return [
            'begin_time'=>'秒杀开始时间',
            'end_time'=>'秒杀结束时间',
            'price'=>'价格',
            'people'=>'已参与人数',
        ];
    }

    //关联查询书名
    public function getBook(){
        return $this->hasOne(Book::className(),['id'=>'book_id']);
    }

}