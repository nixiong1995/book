<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Advert extends ActiveRecord{
    public $file;
    const SCENARIO_ADD ='add';

    public function rules()
    {
        return [
            ['file', 'file', 'extensions' => ['png', 'jpg', 'gif']],
            ['file','required','on'=>self::SCENARIO_ADD],
            [['position','sort'],'required'],
            ['sort','integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'file'=>'广告图片',
            'position'=>'广告位置',
            'sort'=>'排序',
            'count'=>'统计'
        ];
    }

}