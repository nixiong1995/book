<?php
namespace backend\models;
use yii\db\ActiveRecord;

class User extends ActiveRecord{
    public $file;
   /* public $password;//表单密码(未加密)
    const SCENARIO_Add ='add'; //常量定义场景

    //验证规则
    public function rules()
    {
        return [
            ['uid', 'filter', 'filter' => 'trim'],
            [['uid','status'],'required'],
            ['uid', 'unique','targetClass' => '\common\models\User','message' => '账号已存在.'],
            ['uid', 'string', 'min' => 2, 'max' => 10],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique','targetClass' => '\common\models\User','message' => '邮箱名已存在.'],
            ['password','required','on'=>self::SCENARIO_Add],
            ['password', 'string', 'min' => 6, 'max' => 16,'targetClass' => '\common\models\User', 'message' => '{attribute}是6-16位数字或字母'],
            ['tel', 'required'],
            ['tel', 'filter', 'filter' => 'trim'],
            ['tel','match','pattern'=>'/^[1][34578][0-9]{9}$/'],
            ['tel', 'unique', 'targetClass' => '\common\models\User', 'message' => '手机号已被使用'],
        ];
    }*/


}