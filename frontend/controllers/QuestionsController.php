<?php

//元宵节活动题库接口
namespace frontend\controllers;
use backend\models\Question;
use frontend\models\Member;
use yii\web\Controller;
use yii\web\Response;
header("Access-Control-Allow-Origin: *");
class QuestionsController extends Controller{
    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //记录用户出题
    public function actionRecord(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            $title=\Yii::$app->request->post('title');
            $options=\Yii::$app->request->post('option');
            //判断是否传入参数
            if(empty($phone) || empty($title) || empty($options)){
                $relust['msg']='请传入指定参数';
                return $relust;
            }

            //判断选项是否传入索引数组
            if(Question::is_assoc($options)){
                $relust['msg']='选项不是索引数组';
                return $relust;
            }
            //判断数据库是否有该用户
            $member=Member::findOne(['phone'=>$phone]);
            if(!$member){
                $relust['msg']='没有该用户';
                return $relust;
            }
            //记录正确答案
            $correct=$options[0];
            //打乱选项
            shuffle($options);
            //记录用户出题
            $model=new Question();
            $model->title=$title;
            $model->a=$options[0];
            $model->b=$options[1];
            $model->c=$options[2];
            $model->d=$options[3];
            $model->correct=$correct;
            $model->status=1;
            $model->ascription=$phone;
            $model->create_time=time();
            if($model->save()){
                $relust['msg']='出题成功';
                $relust['code']=200;
            }else{
                $relust['msg']='出题失败';
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //查询用户出题
    public function actionMemberQuestions(){
        $relust=[
          'code'=>400,
          'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            //查询用户出题
            $model=Question::find()->where(['ascription'=>$phone])->all();
            //判断是否出题
            if(!$model){
                $relust['msg']='该手机未出题';
                return $relust;
            }
            $relust['code']=200;
            $relust['msg']='获取用户出题成功';
            $relust['data']=$model;

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }
}