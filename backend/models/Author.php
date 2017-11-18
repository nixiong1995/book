<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Author extends ActiveRecord{
    public $file;

    public function rules()
    {
        return [
            [['name','intro'],'required'],
            ['file','file','extensions'=>['jpg','png','gif']],
            ['popularity','number']
            ];
    }

    public function attributeLabels()
    {
        return [
            'name'=>'姓名',
            'intro'=>'简介',
            'file'=>'图片',
            'popularity'=>'人气',
        ];
    }
}