<?php
namespace backend\models;
use yii\db\ActiveRecord;

class App extends ActiveRecord{

    public $file;

    public function rules()
    {
        return [
            [['version','intro','type','file'],'required']
        ];
    }

    public function attributeLabels()
    {
        return [
            'version'=>'版本号',
            'type'=>'类型',
            'intro'=>'版本描述',
            'file'=>'app文件',
        ];
    }
}