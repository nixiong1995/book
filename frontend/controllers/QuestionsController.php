<?php

//元宵节活动题库接口
namespace frontend\controllers;
use backend\models\Question;
use frontend\models\Member;
use yii\web\Controller;
use yii\web\Response;
header("Access-Control-Allow-Origin: http://www.voogaa.cn");
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
            $options=explode(',',$options);
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
            //判断数据库题是否达到2000
            $num=Question::find()->andWhere(['status'=>4])->count('id');
            if($num>2000){
                $relust['msg']='出题已达到上限';
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

    //用户查询自己出题以及红包
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
            $money=\Yii::$app->db->createCommand("SELECT money FROM member WHERE phone='$phone'")->queryScalar();
            //判断是否出题
            if(!$model){
                $relust['msg']='该手机未出题';
                return $relust;
            }
            $relust['code']=200;
            $relust['msg']='获取用户出题成功';
            $relust['money']=$money;
            $relust['data']=$model;

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //随机出题,用户答题
    public function actionQueryQuestions(){

        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            $deduction=\Yii::$app->request->post('deduction');
            //活动时间
            $date=\Yii::$app->request->post('date');
            //判断是否传入参数
            if(empty($phone) || empty($date)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            $member=Member::findOne(['phone'=>$phone]);
            //判断是否有该用户
            if(!$member){
                $relust['msg']='没有该手机号';
                return $relust;
            }

          //判断活动时间
            if($date==20180301){
                //判断今日答题次数
                if($member->today<=0){
                    $relust['msg']='今日答题次数已用完';
                    return $relust;
                }
            }elseif($date==20180302){
                //判断今日答题次数
                if($member->one<=0){
                    $relust['msg']='今日答题次数已用完';
                    return $relust;
                }

            }elseif ($date>20180303){
                if($member->two<0){
                    $relust['msg']='今日答题次数已用完';
                    return $relust;
                }
            }elseif($date<20180301){
                $relust['msg']='活动未开始';
                return $relust;
            }elseif ($date>20180303){
                $relust['msg']='活动已结束';
                return $relust;
            }

            //判断是否扣减答题次数
            if($deduction){
                if($date==20180301){
                    $member->today=$member->today-1;
                    $member->save();
                }elseif($date==20180302){
                    $member->one=$member->one-1;
                    $member->save();
                }elseif($date==20180303){
                    $member->two=$member->two-1;
                    $member->save();
                }
            }

            $models=Question::findBySql('SELECT * FROM question WHERE status=4 order by rand() limit 1')->all();
            $relust['code']=200;
            $relust['msg']='获取题库成功';
            $relust['data']=$models;
        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }
}