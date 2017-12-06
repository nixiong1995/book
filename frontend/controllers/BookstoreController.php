<?php
namespace frontend\controllers;
use yii\web\Controller;
use yii\web\Response;

class BookstoreController extends Controller{

    public $enableCsrfValidation=false;
    public $token = 'yuekukuyue666888';

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionIndex(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'adverts'=>[],
            'reads'=>[],
            'seckills'=>[],
            'likes'=>[],
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                $result['msg']= $res;
            }else{

            }


        }else{
            $result['msg']='请求方式错误';
        }

    }
}