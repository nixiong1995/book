<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Information;
use yii\data\Pagination;
use yii\web\Controller;

class InformationController extends Controller{

    public function actionIndex(){
        $query=Information::find();
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>10,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);

    }

    //添加
    public function actionAdd(){
        $model=new Information();
        $model->scenario=Information::SCENARIO_Add;//指定当前场景为SCENARIO_Add
        $requset=\Yii::$app->request;
        if($requset->isPost){
            $model->load($requset->post());
            if($model->validate()){
                $model->create_time=time();
                $model->save();
                \Yii::$app->session->setFlash('success','添加成功');
                return $this->redirect(['information/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //修改
    public function actionEdit($id){
        $model=Information::findOne(['id'=>$id]);
        $requset=\Yii::$app->request;
        if($requset->isPost){
            $model->load($requset->post());
            if($model->validate()){
                $model->create_time=time();
                $model->save();
                \Yii::$app->session->setFlash('success','修改成功');
                return $this->redirect(['information/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //删除
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $res=Information::findOne(['id'=>$id])->delete();
        if($res){
            return 'success';
        }else{
            return 'error';
        }
    }

    //验证访问权限
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