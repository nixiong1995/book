<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Brush;
use yii\data\Pagination;
use yii\web\Controller;

class BrushController extends Controller{

    public function actionIndex(){
        $date=\Yii::$app->request->get('keyword');
        $time=date("Ymd");
        if($date){
            $query=Brush::find()->where(['date'=>$date]);
        }else{
            $query=Brush::find()->where(['date'=>$time]);
        }
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>20,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);

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