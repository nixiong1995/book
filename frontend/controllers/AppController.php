<?php
namespace frontend\controllers;
use backend\models\App;
use yii\web\Controller;
use yii\web\Response;

class  AppController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionGetApp(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
           $model=App::find()->orderBy('create_time DESC')->one();
           if($model){
               $result['code']=200;
               $result['msg']='获取app成功';
               $result['data']=['version'=>$model->version,'intro'=>$model->intro,
                   'url'=>$model->url,'type'=>$model->type,'create_time'=>$model->create_time];
           }else{
               $result['msg']='没有app';
           }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}