<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Author;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\UploadedFile;

class AuthorController extends Controller{

    //作者列表
    public function actionIndex(){
        $keyword=\Yii::$app->request->get('keyword');
        $query=Author::find()->where(['status'=>1])->orderBy(['popularity'=>SORT_DESC]);
        if($keyword){
            $query->andWhere(['like','name',$keyword]);
        }
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>20,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);
    }

    //作者添加
    public function actionAdd()
    {
        $model = new Author();
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $model->load($request->post());
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {//验证规则
                if($model->file){
                    $dir =  UPLOAD_PATH . date("Y").'/'.date("m").'/'.date("d").'/';
                    if (!is_dir($dir)) {
                        mkdir($dir,0777,true);
                    }
                    $fileName = uniqid() . rand(1, 100000) .'.'.$model->file->extension;
                    $dir = $dir . "/" . $fileName;
                    //移动文件
                    $model->file->saveAs($dir, false);
                    $uploadSuccessPath =date("Y").'/'.date("m").'/'.date("d").'/'. $fileName;
                    $model->image = $uploadSuccessPath;
                }
                $model->status=1;
                $model->create_time=time();
                $model->save();
                \Yii::$app->session->setFlash('success', '添加成功');
                //跳转
                return $this->redirect(['author/index']);

            }
        }
        return $this->render('add', ['model' => $model]);
    }

    //作者修改
    public function actionEdit($id)
    {
        //地址栏参数
        $data=\Yii::$app->request->get('data');
        if($data){
            //将数组通过&符号链接
            $s = urldecode(http_build_query($data));
        }
        $model = Author::findOne(['id' => $id]);
        $model->file = $model->image;
        $old_path=$model->file;
        $request = \Yii::$app->request;
        if ($request->isPost) {
            //模型加载数据
            $model->load($request->post());
            //处理上传文件
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {//验证规则
                if($model->file){
                    $dir = UPLOAD_PATH . date("Y").'/'.date("m").'/'.date("d").'/';
                    if (!is_dir($dir)) {
                        mkdir($dir,0777,true);
                    }
                    $fileName = uniqid() . rand(1, 100000) . '.' . $model->file->extension;
                    $dir = $dir . "/" . $fileName;
                    //移动文件
                    $model->file->saveAs($dir, false);
                    $uploadSuccessPath =date("Y").'/'.date("m").'/'.date("d").'/'. $fileName;
                    $model->image = $uploadSuccessPath;
                    //如果有旧文件,删除旧文件
                    if($old_path){
                        $old_path=UPLOAD_PATH.$old_path;
                        unlink($old_path);//删除原文件
                    }
                }
                //保存所有数据
                $model->save();
                \Yii::$app->session->setFlash('success', '修改成功');
                //跳转
                return $this->redirect(['author/index?'.$s]);
            }
        }
        return $this->render('add', ['model' => $model]);
    }

    //作者删除
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $author=Author::findOne(['id'=>$id]);
        $path=$author->image;//作者头像路径
        //如果有删除作者头像
        if($path){
            $path=UPLOAD_PATH.$path;
            unlink($path);
        }
        $res=$author->delete();
        if( $res){
            return 'success';
        }else{
            return 'error';
        }
    }

    //作者推荐
    public function actionGroom(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $author=Author::findOne(['id'=>$id]);
        if( $author){
            $author->hot_time=time();
            $author->groom=1;
            $author->save();
            return 'success';
        }else{
            return 'error';
        }
    }

    //作者推荐列表
    public function actionGroomIndex(){
        $models=Author::find()->where(['groom'=>1])->orderBy('hot_time DESC')->limit(6)->all();
        return $this->render('groom-index',['models'=>$models]);
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