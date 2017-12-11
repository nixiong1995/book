<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Author;
use backend\models\Book;
use backend\models\Category;
use backend\models\GroomForm;
use backend\models\LoginForm;
use libs\Read;
use yii\bootstrap\Html;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\UploadedFile;

class BookController extends Controller{

    //图书列表
    public function actionIndex(){
        $category_id=\Yii::$app->request->get('category');
        $book=\Yii::$app->request->get('book');//书名
        $author=\Yii::$app->request->get('author');//作者
        //查询数据库分类第一位
        if(!$book && !$author){
            $id=\Yii::$app->db->createCommand("SELECT id FROM category ")->queryScalar();
            $category_id=$category_id?$category_id:$id;
        }
        $where='';
        //$query=Book::find()->where(['status'=>1,'category_id'=>$category_id])->orderBy('groom_time DESC');
        if($author){
            $author_id=\Yii::$app->db->createCommand("SELECT id FROM author WHERE name='$author'")->queryScalar();
            //var_dump($author_id);exit;
            $where=" and author_id='$author_id'";
            //$query ->andWhere(['like','author_id',$author_id]);
        }
        if ($book){
            $where.=" and name like '%$book%'";
            //$query->andWhere(['like','name',$book]);
        }
        if ($category_id){
            $where.=" and category_id='$category_id'";
            //$query->andWhere(['category_id'=>$category_id]);
        }

        $query=Book::findBySql("SELECT * FROM book WHERE status=1 $where ORDER by groom_time DESC ");
        //实例化分页工具类
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>10,//每页显示条数
        ]);
        //分页查询
        $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);

    }

    //图书添加
    public function actionAdd(){
        $model=new Book();
        $model->scenario=Book::SCENARIO_ADD;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            $model->file=UploadedFile::getInstance($model,'file');
            if ($model->validate()) {//验证规则
                    $dir =UPLOAD_PATH .date("Y").'/'.date("m").'/'.date("d").'/';
                    if (!is_dir($dir)) {
                        mkdir($dir,0777,true);
                    }
                    $fileName = date("HiiHsHis") . '.' . $model->file->extension;
                    $dir = $dir . "/" . $fileName;
                    //移动文件
                    $model->file->saveAs($dir, false);
                    $uploadSuccessPath = date("Y").'/'.date("m").'/'.date("d").'/' . $fileName;
                    $model->image = $uploadSuccessPath;
                    $model->status=1;
                    $model->create_time=time();
                    //保存所有数据
                    $model->save();
                    \Yii::$app->session->setFlash('success', '添加成功');
                    //跳转
                    return $this->redirect(['book/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //图书修改
    public function actionEdit($id)
    {
        $model = Book::findOne(['id' => $id]);
        $model->file = $model->image;
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $model->load($request->post());
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {//验证规则
                if($model->file){
                    $dir = UPLOAD_PATH .date("Y").'/'.date("m").'/'.date("d").'/';
                    if (!is_dir($dir)) {
                        mkdir($dir,0777,true);
                    }
                    $fileName = date("HiiHsHis") . '.' . $model->file->extension;
                    $dir = $dir . "/" . $fileName;
                    //移动文件
                    $model->file->saveAs($dir, false);
                    $uploadSuccessPath = date("Y").'/'.date("m").'/'.date("d").'/' . $fileName;
                    $model->image = $uploadSuccessPath;
                }
                    //保存所有数据
                    $model->save();
                    \Yii::$app->session->setFlash('success', '修改成功');
                    //跳转
                    return $this->redirect(['book/index']);
            }
        }
        return $this->render('add', ['model' => $model]);
    }

    //图书下架
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $book=Book::findOne(['id'=>$id]);
        if($book){
            $book->status=0;
            $book->save();
            return 'success';
        }else{
            return 'error';
        }
    }

    //加入分类精选
    public function actionSelected(){
        $id=\Yii::$app->request->post('id');
        $book=Book::findOne(['id'=>$id]);
        if($book){
            $book->groom_time=time();
            $book->save();
            return 'success';
        }else{
            return 'error';
        }
    }

    //加入推荐
    public function actionGroom(){
        $book_id=\Yii::$app->request->get('book_id');
        $model=new GroomForm();
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $book=Book::findOne(['id'=>$book_id]);
                $book->groom=$model->groom;
                $book->groom_time=time();
                $book->save();
                \Yii::$app->session->setFlash('success', '添加推荐成功');
                return $this->redirect(['book/index']);
            }
        }
        return $this->render('groom-add',['model'=>$model]);
    }

    //今日必读列表列表
    public function actionTodayRead(){
        $models=Book::find()->where(['groom'=>1])->orderBy('groom_time DESC')->limit(5)->all();
        return $this->render('today-read',['models'=>$models]);
    }

    //今日限免列表
    public function actionTodayFree(){
        $models=Book::find()->where(['groom'=>2])->orderBy('groom_time DESC')->limit(3)->all();
        return $this->render('today-free',['models'=>$models]);
    }

    //女生限免
    public function actionGirlFree(){
        $models=Book::find()->where(['groom'=>3])->orderBy('groom_time DESC')->limit(8)->all();
        return $this->render('girl-free',['models'=>$models]);
    }

    //男生限免
    public function actionBoyFree(){
        $models=Book::find()->where(['groom'=>4])->orderBy('groom_time DESC')->limit(3)->all();
        return $this->render('boy-free',['models'=>$models]);
    }

    //男生完本限免
    public function actionEndFree(){
        $models=Book::find()->where(['groom'=>5])->orderBy('groom_time DESC')->limit(6)->all();
        return $this->render('end-free',['models'=>$models]);
    }

    //女生完本限免
    public function actionGirlEndfree(){
        $models=Book::find()->where(['groom'=>6])->orderBy('groom_time DESC')->limit(6)->all();
        return $this->render('girl-endfree',['models'=>$models]);
    }

    public function actionRead(){
        $file = BOOK_PATH.'20171121/wandaojianzun.txt';
        $exts = get_loaded_extensions();
        $mimeType ='application/octet-stream';
        if(array_search('fileinfo', $exts)===FALSE)
        {
            $sizeInfo = getimagesize($file);
            $mimeType = $sizeInfo['mime'];
        }else{
            $mimeType = mime_content_type($file);
        }
        $read=new Read();
        $read->smartReadFile($file,$mimeType);
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