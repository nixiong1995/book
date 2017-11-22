<?php
namespace backend\models;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
use yii\db\ActiveRecord;

class LoginForm extends Model
{
    public $code;
    public $username;
    public $password;
    public $remember ;


    public function rules()
    {
        return [
            ['code','captcha','captchaAction'=>'admin/captcha'],
            [['username','password'],'required'],
            ['remember','string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'username'=>'用户名',
            'password'=>'密码',
            'code'=>'验证码',
            'remember'=>'记住密码',

        ];
    }


    public function login(){
        //根据用户查询数据表
        $admin=Admin::findOne(['username'=>$this->username]);
        if($admin){
            //查询到该用户,验证密码
            //var_dump(\Yii::$app->security->validatePassword($this->password,$admin->password_hash));exit;
            if(\Yii::$app->security->validatePassword($this->password,$admin->password_hash)){
                //密码争取允许登录
                $admin->last_login_time=time();
                $admin->last_login_ip=\Yii::$app->request->userIP;
                $admin->save();
                if($this->remember){
                    return \Yii::$app->user->login($admin,7*24*3600);
                }else{
                    return \Yii::$app->user->login($admin);
                }
            }else{
                //密码错误
                $this->addError('password','密码错误');
            }
        }else{
            //没有查到改用户
            $this->addError('username','没有改用户');
        }
        return false;
    }

}