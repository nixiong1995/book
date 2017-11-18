<?php
namespace backend\controllers;
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
        }else{
            $query->all();
        }
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>10,//每页显示条数
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
            $dir =  'D:/WWW/yii2book/uploads/' . date("Ymd");
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            if ($model->validate()) {//验证规则
                if($model->file){
                    $fileName = date("HiiHsHis") . $model->file->baseName . '.' . $model->file->extension;
                    $dir = $dir . "/" . $fileName;
                    //移动文件
                    $model->file->saveAs($dir, false);
                    $uploadSuccessPath =date("Ymd") . "/" . $fileName;
                    $model->image = $uploadSuccessPath;
                }
                $model->status=1;
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
        $model = Author::findOne(['id' => $id]);
        $model->file = $model->image;
        $request = \Yii::$app->request;
        if ($request->isPost) {
            //模型加载数据
            $model->load($request->post());
            //处理上传文件
            $model->file = UploadedFile::getInstance($model, 'file');
            $dir = \Yii::getAlias('@webroot') . '/uploads/' . date("Ymd");
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            if ($model->validate()) {//验证规则
                $fileName = date("HiiHsHis") . $model->file->baseName . '.' . $model->file->extension;
                $dir = $dir . "/" . $fileName;
                //移动文件
                $model->file->saveAs($dir, false);
                $uploadSuccessPath = "/uploads/" . date("Ymd") . "/" . $fileName;
                $model->image = $uploadSuccessPath;
                //保存所有数据
                $model->save();
                \Yii::$app->session->setFlash('success', '修改成功');
                //跳转
                return $this->redirect(['author/index']);
            }
        }
        return $this->render('add', ['model' => $model]);
    }

    //作者删除
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $category=Author::findOne(['id'=>$id]);
        if($category){
            $category->status=0;
            $category->save();
            return 'success';
        }else{}
        return 'error';
    }
}