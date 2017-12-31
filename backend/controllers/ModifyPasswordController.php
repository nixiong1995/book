<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\ModifyForm;
use yii\web\Controller;

class ModifyPasswordController extends Controller
{
    public function actionIndex(){
        //根据identity信息判断用户是否登录,登录后才能修改密码
       if(\Yii::$app->user->identity){
           $model=new ModifyForm();
           $request=\Yii::$app->request;
           if($request->isPost){
               //模型加载数据
               $model->load($request->post());
               if($model->validate()){
                           //用户输入的旧密码和数据库中的密码对比成功
                           $admin=\Yii::$app->user->identity;
                           $admin->password=$model->new_password;
                           $admin->save();
                           \Yii::$app->session->setFlash('success','修改密码成功');
                           return $this->redirect(['book/index']);
                       }
               }
           return $this->render('index',['model'=>$model]);
        }else{
           //没有登录,跳转到登录页面.展示提示信息
           \Yii::$app->session->setFlash('error','请登录后操作');
           return $this->redirect(['admin/login']);
       }

    }

    //验证码
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                //设置验证码参数
                'minLength' => 4,
                'maxLength' => 4,
            ],
        ];
    }
}