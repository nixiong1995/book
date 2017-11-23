<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use yii\web\Controller;

class PurchasedController extends Controller{

    public function actionIndex(){
        $models=\backend\models\Purchased::find()->all();
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