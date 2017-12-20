<?php
namespace backend\controllers;
use backend\models\App;
use yii\web\Controller;

class AppController extends Controller{

    //app列表
    public function actionIndex(){

    }

    //app发布
    public function actionAdd(){
        $model=new App();
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
        }
        return $this->render('add',['model'=>$model]);
    }

}