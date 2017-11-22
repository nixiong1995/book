<?php
namespace backend\controllers;
use yii\web\Controller;

class PurchasedController extends Controller{

    public function actionIndex(){
        $models=\backend\models\Purchased::find()->all();
        return $this->render('index',['models'=>$models]);
    }
}