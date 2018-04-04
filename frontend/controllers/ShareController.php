<?php
namespace frontend\controllers;
use frontend\models\Share;
use yii\web\Controller;
use yii\web\Response;

//app分享标题和描述
class ShareController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionIndex(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            $model=Share::find()->one();
            if($model){
                $result['code']=200;
                $result['msg']='成功返回内容';
                $result['data']=$model;

            }else{
                $result['msg']='无分享内容';
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}