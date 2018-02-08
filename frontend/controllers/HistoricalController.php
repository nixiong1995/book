<?php
namespace frontend\controllers;

use frontend\models\Historical;
use libs\PostRequest;
use yii\web\Controller;
use yii\web\Response;

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
            var_dump($datas['content']['data']);exit;

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}