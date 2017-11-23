<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Book;
use libs\Read;
use yii\web\Controller;
use yii\web\UploadedFile;

class BookController extends Controller{

    //图书列表
    public function actionIndex(){
        /*var_dump(\Yii::$app->user->isGuest);
        var_dump(\Yii::$app->user->id);
        var_dump(\Yii::$app->user->identity);exit;*/
        $models=Book::findAll(['status'=>1]);
        return $this->render('index',['models'=>$models]);

    }

    //图书添加
    public function actionAdd(){
        $model=new Book();
        $model->scenario=Book::SCENARIO_ADD;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            $model->file=UploadedFile::getInstance($model,'file');
            $model->book=UploadedFile::getInstance($model,'book');
            if ($model->validate()) {//验证规则
                    $dir =UPLOAD_PATH .date("Ymd");
                    if (!is_dir($dir)) {
                        mkdir($dir);
                    }
                    $fileName = date("HiiHsHis") . '.' . $model->file->extension;
                    $dir = $dir . "/" . $fileName;
                    //移动文件
                    $model->file->saveAs($dir, false);
                    $uploadSuccessPath = date("Ymd") . "/" . $fileName;
                    $model->image = $uploadSuccessPath;
                    $pin = new \libs\Pin();
                    $name=$pin->Pinyin($model->name,'UTF8');//将书名转成拼音
                    $path=BOOK_PATH.date("Ymd");
                    if (!is_dir( $path)) {
                        mkdir( $path);
                    }
                    $fileName2=$name.'.'.$model->book->extension;
                    $path=$path.'/'.$fileName2;
                    $model->book->saveAs($path,false);
                    $bookPath=date("Ymd").'/'.$fileName2;
                    $model->path=$bookPath;
                    $type=substr(strrchr($model->book->name, '.'), 1);
                    $model->size=$model->book->size;
                    $model->type=$type;
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
            $model->book=UploadedFile::getInstance($model,'book');
            if ($model->validate()) {//验证规则
                if($model->file){
                    $dir = UPLOAD_PATH . date("Ymd");
                    if (!is_dir($dir)) {
                        mkdir($dir);
                    }
                    $fileName = date("HiiHsHis") . '.' . $model->file->extension;
                    $dir = $dir . "/" . $fileName;
                    //移动文件
                    $model->file->saveAs($dir, false);
                    $uploadSuccessPath = date("Ymd") . "/" . $fileName;
                    $model->image = $uploadSuccessPath;
                }
                if($model->book){
                    $pin = new \libs\Pin();
                    $name=$pin->Pinyin($model->name,'UTF8');//将书名转成拼音
                    $path=BOOK_PATH.date("Ymd");
                    if (!is_dir( $path)) {
                        mkdir( $path);
                    }
                    $fileName2=$name.'.'.$model->book->extension;
                    $path=$path.'/'.$fileName2;
                    $model->book->saveAs($path,false);
                    $bookPath=date("Ymd").'/'.$fileName2;
                    $model->path=$bookPath;
                    $type=substr(strrchr($model->book->name, '.'), 1);
                    $model->size=$model->book->size;
                    $model->type=$type;
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

    public function actionRead(){
        $file = BOOK_PATH.'20171121/wandaojianzun.txt';
        $exts = get_loaded_extensions();
        $mimeType = 'application/octet-stream';
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