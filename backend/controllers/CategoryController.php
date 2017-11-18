<?php
namespace backend\controllers;
use backend\models\Category;
use yii\data\Pagination;
use yii\web\Controller;

class CategoryController extends Controller{

    //分类列表
    public function actionIndex(){
        $query=Category::find();
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>10,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        //调用视图展示数据
        return $this->render('index',['models'=>$models,'pager'=>$pager]);
    }

    //分类添加
    public function actionAdd(){
        $model=new Category();
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $model->save();
                \Yii::$app->session->setFlash('success','添加成功');
                return $this->redirect(['category/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //分类修改
    public function actionEdit($id){
        $model=Category::findOne(['id'=>$id]);
        $request=\Yii::$app->request;
        if($request->isPost){
            //模型加载数据
            $model->load($request->post());
            if($model->validate()) {
                $model->save();
                \Yii::$app->session->setFlash('success','修改成功');
                return $this->redirect(['category/index']);
            }
        }
            return $this->render('add',['model'=>$model]);
    }

    //分类删除
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $category=Category::findOne(['id'=>$id]);
        if($category){
            $category->status=0;
            $category->save();
            return 'success';
        }else{}
        return 'error';
    }
}