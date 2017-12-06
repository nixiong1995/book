<?php
namespace backend\models;
use yii\base\Model;
use yii\db\ActiveRecord;

class ModifyForm extends Model
{
    public $username;
    public $old_password;
    public $new_password;
    public $repeat_new_password;
    public $code;

    //验证规则
    public function rules()
    {
        return [
            [['old_password','new_password','repeat_new_password'],'required'],
            ['code','captcha','captchaAction'=>'modify-password/captcha'],
            ['repeat_new_password','compare','compareAttribute'=>'new_password','message'=>'两次密码输入不一致'],
            ['old_password','validatePassword'],
        ];
    }

    //定义验证旧密码规则
    public function validatePassword(){
        //var_dump(\Yii::$app->security->validatePassword($this->old_password,\Yii::$app->user->identity->password_hash));exit;
        if(!\Yii::$app->security->validatePassword($this->old_password,\Yii::$app->user->identity->password_hash)){
            $this->addError('old_password','旧密码错误');
        };
    }

    //字段中文命名
    public function attributeLabels()
    {
        return [
           'username'=>'用户名',
            'new_password'=>'新密码',
            'old_password'=>'旧密码',
            'repeat_new_password'=>'确认新密码',
            'code'=>'验证码',
        ];
    }

}