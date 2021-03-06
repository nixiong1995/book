<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Author extends ActiveRecord{
    public $file;

    public function rules()
    {
        return [
            [['name','intro','type'],'required'],
            ['file','file','extensions'=>['jpg','png','gif','jpeg']],
            ['popularity','number'],
            ['name','unique'],
            ['sign','safe'],
            ['type','string'],
            ];
    }

    public function attributeLabels()
    {
        return [
            'name'=>'姓名',
            'intro'=>'简介',
            'file'=>'图片',
            'popularity'=>'人气',
            'sign'=>'是否签约作者',
            'type'=>'擅长的类型',
        ];
    }
}