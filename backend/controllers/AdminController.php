<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\LoginForm;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AdminController extends Controller
{
    //查询所有管理员
    public function actionIndex(){
        $query=Admin::find();
        //实现分页类
        $pager=new Pagination([
            'totalCount'=>$query->count(),
            'defaultPageSize'=>5
        ]);
        //分页查询
        $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);
        //var_dump(\Yii::$app->user->identity);
    }

    //添加管理员
    public function actionAdd(){
        $model=new Admin();
        $model->scenario=Admin::SCENARIO_Add;//指定当前场景为SCENARIO_Add
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $model->save();
                \Yii::$app->session->setFlash('success','添加成功');
                return $this->redirect(['admin/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //修改管理员
    public function actionEdit($id){
        $model=Admin::findOne(['id'=>$id]);
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $model->save();
                \Yii::$app->session->setFlash('success','修改成功');
                return $this->redirect(['admin/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //删除管理员
    public function actionDel(){
        $id=\Yii::$app->request->post('id');
        $model=Admin::findOne(['id'=>$id])->delete();
        if($model){
            return 'success';
        }else{
            return 'fail';
        }
    }

    //登录功能
    public function actionLogin(){
        $model=new LoginForm();
        $request=\Yii::$app->request;
        if($request->isPost){
            //模型加载数据
            $model->load($request->post());
            if($model->validate()){
                if($model->login()){
                    //提示信息
                    \Yii::$app->session->setFlash('success','登录成功');
                    //跳转
                    return $this->redirect(['book/index']);
                }
            }
        }
        return $this->render('login',['model'=>$model]);
    }

    //退出登录
    public function actionLogout(){
        \Yii::$app->user->logout();
        return $this->redirect(['admin/login']);
    }

    //验证码
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                //设置验证码参数
                'minLength' => 4,
                'maxLength' => 4,
            ],
        ];
    }


}