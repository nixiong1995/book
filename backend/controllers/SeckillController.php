<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Seckill;
use yii\web\Controller;

class SeckillController extends Controller{

    //秒杀列表
    public function actionIndex(){
        $models=Seckill::find()->orderBy('create_time Desc')->limit(4)->all();
        return $this->render('index',['models'=>$models]);
    }

    //加入秒杀,修改秒杀
    public function actionAdd($book_id){
        $seckill=Seckill::findOne(['book_id'=>$book_id]);
        $requset=\Yii::$app->request;
        if($seckill){
            if($requset->isPost){
               $seckill->load($requset->post());
               if($seckill->validate()){
                   $seckill->book_id=$book_id;
                   $seckill->create_time=time();
                   $seckill->save();
                   \Yii::$app->session->setFlash('success', '加入秒杀成功');
                   //跳转
                   return $this->redirect(['seckill/index']);
               }
            }
            return $this->render('add',['model'=> $seckill]);

        }else{
            $model=new Seckill();
            if($requset->isPost){
                $model->load($requset->post());
                if($model->validate()){
                    $model->book_id=$book_id;
                    $model->create_time=time();
                    $model->save();
                    \Yii::$app->session->setFlash('success', '修改秒杀成功');
                    //跳转
                    return $this->redirect(['seckill/index']);
                }
            }
            return $this->render('add',['model'=>$model]);

        }
    }

    public function behaviors()
    {
        return [
            'rbac'=>[
                'class'=>RbacFilter::className(),
                'except'=>['login','logout','captcha','error'],
            ]
        ];
    }
}