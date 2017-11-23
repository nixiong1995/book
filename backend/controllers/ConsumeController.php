<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Consume;
use yii\web\Controller;

class ConsumeController extends Controller{

    public function actionIndex(){
        $models=Consume::find()->all();
        return $this->render('index',['models'=>$models]);
    }

    public function behaviors()
    {
        return [
            'rbac'=>[
                'class'=>RbacFilter::className(),
                'except'=>['login','logout','captcha','error'],
            ]
        ];
    }

}