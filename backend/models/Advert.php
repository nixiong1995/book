<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Advert extends ActiveRecord{
    public $file;
    const SCENARIO_ADD ='add';

    public function rules()
    {
        return [
            ['file', 'file', 'extensions' => ['png', 'jpg', 'gif','jpeg']],
            ['file','required','on'=>self::SCENARIO_ADD],
            [['position','sort','title','url','client','version'],'required'],
            [['sort','checked'],'integer'],
            ['url','string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'file'=>'广告图片',
            'position'=>'广告位置',
            'sort'=>'排序',
            'url'=>'图片链接地址',
            'title'=>'广告标题',
            'client'=>'客户端',
            'version'=>'版本号',
            'checked'=>'苹果审核广告请勾选',
        ];
    }

}