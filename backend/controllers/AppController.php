<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\App;
use yii\web\Controller;
use yii\web\UploadedFile;

class AppController extends Controller{

    //app列表
    public function actionIndex(){
        $models=App::find()->all();
        return $this->render('index',['models'=>$models]);

    }

    //app发布
    public function actionAdd(){
        $model=new App();
        $model->scenario=App::SCENARIO_ADD;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            //处理上传文件
            $model->file=UploadedFile::getInstance($model,'file');
            if($model->validate()){//验证规则
                $dir =\Yii::getAlias('@webroot'). '/app';
                if (!is_dir($dir)){
                    mkdir( $dir);
                }
                $fileName ='yueku'.'.'.$model->file->extension;
                $dir = $dir."/". $fileName;
                //移动文件
                $model->file->saveAs($dir,false);
                $uploadSuccessPath = "/app/".$fileName;
                $model->url=$uploadSuccessPath;
                $model->create_time=time();
                $model->save();
                \Yii::$app->session->setFlash('success','添加成功');
                //跳转
                return $this->redirect(['app/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //修改app
    public function actionEdit($id){
        $model=App::findOne(['id'=>$id]);
        $old_path=$model->url;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            //处理上传文件
            $model->file=UploadedFile::getInstance($model,'file');
            if($model->validate()){
                if($model->file){
                    $dir =\Yii::getAlias('@webroot'). '/app';
                    if (!is_dir($dir)){
                        mkdir( $dir);
                    }
                    $fileName = 'yueku'.'.'.$model->file->extension;
                    $dir = $dir."/". $fileName;
                    //移动文件
                    $model->file->saveAs($dir,false);
                    $uploadSuccessPath = "/app/".$fileName;
                    $model->url=$uploadSuccessPath;
                }
                $model->update_time=time();
                $model->save();
                \Yii::$app->session->setFlash('success','修改成功');
                //跳转
                return $this->redirect(['app/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //删除app
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $model=App::findOne(['id'=>$id]);
        $path=\Yii::getAlias('@webroot').$model->url;
        $res1=$model->delete();
        $res2=unlink($path);
        if($res1 && $res2){
            return 'success';
        }else{
            return 'error';
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