<?php
namespace backend\models;
use yii\base\Model;

class GroomForm extends Model{
    public $groom;

    public function attributeLabels()
    {
        return [
            'groom'=>'推荐到'
        ];
    }

    public function rules()
    {
        return [
            ['groom','required'],
        ];
    }
}