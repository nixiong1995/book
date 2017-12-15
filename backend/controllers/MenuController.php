<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Menu;
use yii\data\Pagination;
use yii\web\Controller;

class MenuController extends Controller
{
    //菜单列表
    public function actionIndex(){
        $query=Menu::find();
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>20,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);
    }

    //菜单添加
    public function actionAdd(){
        $model=new Menu();
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $model->save();
                \Yii::$app->session->setFlash('success','添加成功');
                return $this->redirect(['menu/add']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //菜单修改
    public function actionEdit($id){
        $model=Menu::findOne(['id'=>$id]);
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $model->save();
                \Yii::$app->session->setFlash('success','修改成功');
                return $this->redirect(['menu/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //菜单删除
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $res=Menu::findOne(['id'=>$id])->delete();
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