<?php
namespace backend\controllers;
use backend\models\Consume;
use yii\web\Controller;

class ConsumeController extends Controller{

    public function actionIndex(){
        $models=Consume::find()->all();
        return $this->render('index',['models'=>$models]);
    }

}