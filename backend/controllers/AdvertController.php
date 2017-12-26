<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Advert;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\UploadedFile;

class AdvertController extends Controller{

    public function actionAdd(){
        $model=new Advert();
        $model->scenario=Advert::SCENARIO_ADD;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            $model->file=UploadedFile::getInstance($model,'file');
            if ($model->validate()) {//验证规则
                $dir =UPLOAD_PATH .date("Y").'/'.date("m").'/'.date("d").'/';
                if (!is_dir($dir)) {
                    mkdir($dir,0777,true);
                }
                $fileName = uniqid() . rand(1, 100000) . '.' . $model->file->extension;
                $dir = $dir . "/" . $fileName;
                //移动文件
                $model->file->saveAs($dir, false);
                $uploadSuccessPath = date("Y").'/'.date("m").'/'.date("d").'/' . $fileName;
                $model->image = $uploadSuccessPath;
                $model->create_time=time();
                //保存所有数据
                $model->save();
                \Yii::$app->session->setFlash('success', '添加成功');
                //跳转
                return $this->redirect(['advert/bookshelf']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //书架广告
    public function actionBookshelf(){
        $position=\Yii::$app->request->get('position');
        $query=Advert::find();//->where(['position'=>0])->orderBy('create_time  DESC');
        if($position){
            $query->where(['position'=>$position]);
        }
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>10,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->orderBy('create_time  DESC')->all();
        return $this->render('bookshelf',['models'=>$models,'pager'=>$pager]);
    }

    //广告修改
    public function actionEdit($id){
        $model=Advert::findOne(['id'=>$id]);
        $model->file =$model->image;
        $old_path=$model->image;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            $model->file=UploadedFile::getInstance($model,'file');
            if ($model->validate()) {//验证规则
                if($model->file){
                    $dir =UPLOAD_PATH .date("Y").'/'.date("m").'/'.date("d").'/';
                    if (!is_dir($dir)) {
                        mkdir($dir,0777,true);
                    }
                    $fileName = uniqid() . rand(1, 100000) . '.' . $model->file->extension;
                    $dir = $dir . "/" . $fileName;
                    //移动文件
                    $model->file->saveAs($dir, false);
                    $uploadSuccessPath = date("Y").'/'.date("m").'/'.date("d").'/' . $fileName;
                    $model->image = $uploadSuccessPath;
                    if($old_path){
                        $old_path=UPLOAD_PATH.$old_path;
                        unlink($old_path);//删除原文件
                    }
                }
                $model->create_time=time();
                //保存所有数据
                $model->save();
                \Yii::$app->session->setFlash('success', '修改成功');
                //跳转
                return $this->redirect(['advert/bookshelf']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //广告删除
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $advert=Advert::findOne(['id'=>$id]);
        $res1=$advert->delete();
        $file = UPLOAD_PATH.$advert->image;
        $res2=unlink($file);
        if($res1&&$res2){
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