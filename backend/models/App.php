<?php
namespace backend\models;
use yii\db\ActiveRecord;

class App extends ActiveRecord{

    public $file;
    const SCENARIO_ADD ='add';


    public function rules()
    {
        return [
            [['version','intro','type'],'required'],
            ['file','required','on'=>self::SCENARIO_ADD],
           // ['file', 'file', 'extensions' => 'apk'],
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