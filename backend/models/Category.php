<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Category extends ActiveRecord{

    public function rules()
    {
        return [
            [['name','intro','type','status'],'required'],
            ['name','unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name'=>'分类名称',
            'intro'=>'简介',
            'type'=>'频道分类',
            'status'=>'状态'
        ];
    }

}