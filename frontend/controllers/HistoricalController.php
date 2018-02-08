<?php

namespace frontend\controllers;

use frontend\models\Historical;
use libs\PostRequest;
use yii\web\Controller;
use yii\web\Response;
header("Access-Control-Allow-Origin: *");
class HistoricalController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format =Response::FORMAT_JSON;
        parent::init();
    }

    //获取历史人物
    public function actionPeople(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){


            $res=true;
            while ($res){

                $models=Historical::findBySql('SELECT * FROM historical WHERE id >= ((SELECT MAX(id) FROM historical)-(SELECT MIN(id) FROM historical)) * RAND() + (SELECT MIN(id) FROM historical)  LIMIT 3')->all();

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

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;

    }

    //获取推荐书
    public function actionBook(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $postUrl = 'http://partner.chuangbie.com/partner/booklist';
            $curlPost =['partner_id'=>2130,'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b','page_size'=>100];
            $post=new PostRequest();
            $data=$post->request_post( $postUrl,$curlPost);
            $datas=json_decode($data,true);
            $indexs=array_rand($datas['content']['data'],3);
            foreach ($indexs as $index){
                $result['data'][]=$datas['content']['data'][$index];
            }
            $result['code']=200;
            $result['msg']='获取书籍成功';

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}