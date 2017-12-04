<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Purchased;
use backend\models\User;
use yii\data\Pagination;
use yii\web\Controller;

class PurchasedController extends Controller{

    public function actionIndex(){
        $tel=\Yii::$app->request->get('tel');//手机
        $query=Purchased::find();
        if($tel){
            $id=\Yii::$app->db->createCommand("SELECT id FROM user WHERE tel='$tel'")->queryScalar();
            $query->where(['user_id'=>$id]);
        }
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>10,//每页显示条数
        ]);
        //分页查询
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