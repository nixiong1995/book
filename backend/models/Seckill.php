<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Seckill extends ActiveRecord{

    public function rules()
    {
        return [
            [['seckill_time','price'],'required'],
            ['people','integer']

        ];
    }

    public function attributeLabels()
    {
        return [
            'seckill_time'=>'秒杀时间',
            'price'=>'价格',
            'people'=>'已参与人数',
        ];
    }

    //关联查询书名
    public function getBook(){
        return $this->hasOne(Book::className(),['id'=>'book_id']);
    }

}