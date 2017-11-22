<?php
namespace backend\controllers;
use backend\models\Recharge;
use yii\web\Controller;

class RechargeController extends Controller{

    public function actionIndex(){
        $models=Recharge::find()->all();
        return $this->render('index',['models'=>$models]);
    }

}