<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Information;
use backend\models\Settlement;
use yii\data\Pagination;
use yii\web\Controller;

class SettlementController extends Controller{

    //本月结算列表
    public function actionIndex(){
        //var_dump(strtotime("20180312"));exit;
        $information=Information::find()->select(['name','id'])->all();
        return $this->render('index',['models'=>$information]);

    }

    //结算添加
    public function actionAdd($information_id){
        $model=new Settlement();
        $model->information_id=$information_id;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $model->information_id=$information_id;
                $model->status=2;
                $model->create_time=time();
                $model->save();
                \Yii::$app->session->setFlash('success','结算成功');
                return $this->redirect(['settlement/index']);
            }
        }else{
            return $this->render('add',['model'=>$model,'information_id'=>$information_id]);
        }

    }

    //历史结算列表
    public function actionHistory(){
        $begin_time=\Yii::$app->request->get('begin_time');//开始时间
        $end_time=\Yii::$app->request->get('end_time');//开始时间
        $information_name=\Yii::$app->request->get('information_name');
        $where='';
        if($begin_time){
            $begin_time= $begin_time.'000000';//拼接时间戳,加上时分秒
            $begin_time=strtotime($begin_time);
            $where.=" and create_time>=$begin_time";
            //$query->andWhere(['>','created_at',$begin_time]);
        }
        if($end_time){
            $end_time=  $end_time.'235959';//拼接时间戳,加上时分秒
            $end_time=strtotime($end_time);
            $where.=" and create_time<=$end_time";
            //$query->andWhere(['<=','created_at',$end_time]);
        }
        if($information_name){
            $information_id=Information::find()->select('id')->where(['like','name',$information_name])->scalar();
            $where.=" and information_id=$information_id";
        }
        $count=\Yii::$app->db->createCommand("SELECT COUNT(id) FROM settlement WHERE 1=1 $where")->queryScalar();
        $pager=new Pagination([
            'totalCount'=>$count,
            'defaultPageSize'=>20
        ]);
        //分页查询
        $models=Settlement::findBySql("SELECT * FROM settlement WHERE 1=1 $where limit $pager->offset,$pager->limit")->All();
        return $this->render('history',['models'=>$models,'pager'=>$pager]);
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