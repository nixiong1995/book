<?php
namespace frontend\controllers;

use frontend\models\Historical;
use yii\web\Controller;
use yii\web\Response;

class HistoricalController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format =Response::FORMAT_JSON;
        parent::init();
    }

    public function actionIndex(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){


            $res=true;
            while ($res){

                $models=Historical::findBySql('SELECT * FROM `historical`
WHERE id >= (SELECT floor(RAND() * (SELECT MAX(id) FROM `historical`)))
ORDER BY id LIMIT 3')->all();

                //判断3个人物不是同一个朝代
                if($models[0]->dynasty==$models[1]->dynasty || $models[1]->dynasty==$models[2]->dynasty || $models[0]->dynasty==$models[2]->dynasty){
                    $res=true;
                }else {
                    $res=false;
                }
            }
            $result['code']=200;
            $result['msg']='获取人物成功';
            $result['data']=$models;
            return $result;


        }else{
            $result['msg']='请求方式错误';
        }
        return $result;

    }
}