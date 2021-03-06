<?php
namespace backend\controllers;
//章节发布
use backend\filters\RbacFilter;
use backend\models\Book;
use backend\models\Chapter;
use backend\models\Uuid;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;

class ChapterController extends Controller{

    //章节列表
    public function actionIndex(){
        $id=\Yii::$app->request->get('id');//图书id
        $keyword=\Yii::$app->request->get('keyword');//搜索关键字
        $res=Chapter::resetPartitionIndex($id);
        if($res!=0){
            $query=Chapter::find()->where(["book_id"=>$id]);
            if($keyword){
                // $query->andFilterWhere(['like','chapter_name',$keyword])
                // ->orFilterWhere(['no'=>$keyword]);
                $query->andWhere(['no'=>$keyword]);
            }
            $pager=new Pagination([
                'totalCount'=>$query->count(),//总条数
                'defaultPageSize'=>20,//每页显示条数
            ]);
            $models=$query->limit($pager->limit)->offset($pager->offset)->orderBy('no DESC')->all();
            return $this->render('index',['models'=>$models,'pager'=>$pager,'book_id'=>$id]);
        }else{
            throw new ForbiddenHttpException('对不起,无可操作数据表');
        }


    }

    //章节添加
    public function actionAdd(){
        //接收书id
        $book_id=\Yii::$app->request->get('book_id');
        $result=Chapter::resetPartitionIndex($book_id);
        if($result!=0){
            $model=new Chapter();
            $model->book_id=$book_id;
            $model->scenario=Chapter::SCENARIO_Add;//指定当前场景为SCENARIO_Add
            $request=\Yii::$app->request;
            if($request->isPost){
                $model->load($request->post());
                $Uuid=new Uuid();
                $Uuid->name='chapter'.$result;
                $Uuid->save();
                $model->id=$Uuid->id;
                $model->file=UploadedFile::getInstance($model,'file');
                if($model->validate()){
                    $dir=BOOK_PATH.date("Y").'/'.date('m').'/'.date('d').'/';
                    if (!is_dir($dir)) {
                        mkdir($dir,0777,true);
                    }
                    $fileName=uniqid() . rand(1, 100000).'.'.$model->file->extension;//文件名
                    $path=$dir.'/'.$fileName;//路径拼上文件名
                    $model->file->saveAs($path,false);//移动文件
                    $bookPath=date("Y").'/'.date('m').'/'.date('d').'/'.$fileName;//数据库保存路径
                    $model->path=$bookPath;
                    $model->word_count=round($model->file->size/3);
                    //保存所有数据
                    $model->create_time=time();
                    $transaction=\Yii::$app->db->beginTransaction();//开启事务
                    try{
                        $model->save();
                        $book=Book::findOne(['id'=>$model->book_id]);
                        $redis=new \Redis();
                        $redis->connect('127.0.0.1');
                        $redis->set($model->book_id,$model->file->size);
                        $book->size=$book->size+$model->file->size;
                        $book->is_end=$model->is_end;
                        $book->last_update_chapter_id=$model->id;
                        $book->last_update_chapter_name=$model->chapter_name;
                        $book->update_time=time();
                        $book->save();
                        $transaction->commit();
                    }catch ( Exception $e){
                        //事务回滚
                        $transaction->rollBack();
                    }
                    \Yii::$app->session->setFlash('success', '添加成功');
                    //跳转
                    return $this->redirect(['chapter/index','id'=>$model->book_id]);
                }
            }
            return $this->render('add',['model'=>$model]);
        }else{
            throw new ForbiddenHttpException('对不起,无可操作数据表');
        }

    }

    //章节修改
    public function actionEdit($id,$book_id)
    {
        $result=Chapter::resetPartitionIndex($book_id);
        if($result!=0){
            $model = Chapter::find()->where(['id' =>$id])->one();
            $model->scenario=Chapter::SCENARIO_EDIT;
            $model->is_end=$model->book->is_end;
            $old_path=$model->path;
            $request = \Yii::$app->request;
            if ($request->isPost) {
                $model->load($request->post());
                $model->file = UploadedFile::getInstance($model, 'file');
                if ($model->validate()) {
                    if($model->file){
                        $dir=BOOK_PATH.date("Y").'/'.date('m').'/'.date('d').'/';
                        if (!is_dir( $dir)) {
                            mkdir( $dir,0777,true);
                        }
                        $fileName=uniqid() . rand(1, 100000).'.'.$model->file->extension;//文件名
                        $path=$dir.'/'.$fileName;//路径拼上文件名
                        $model->file->saveAs($path,false);//移动文件
                        $bookPath=date("Y").'/'.date('m').'/'.date('d').'/'.$fileName;//数据库保存路径
                        $model->path=$bookPath;
                        $model->word_count=round($model->file->size/3);
                        if($old_path){
                            $old_path=BOOK_PATH.$old_path;
                            unlink($old_path);//删除原文件
                        }
                    }
                    //保存所有数据
                    $model->update_time = time();
                    $transaction=\Yii::$app->db->beginTransaction();//开启事务
                    try{
                        $model->save();
                        $book=Book::findOne(['id'=>$model->book_id]);
                        if($model->file){
                            //var_dump($book);exit;
                            $redis=new \Redis();
                            $redis->connect('127.0.0.1');
                            $old_size=$redis->get($model->book_id);
                            $book->size=($book->size-$old_size)+$model->file->size;
                            $redis->set($model->book_id,$model->file->size);
                        }
                        $book->is_end=$model->is_end;
                        $book->last_update_chapter_id=$model->id;
                        $book->last_update_chapter_name=$model->chapter_name;
                        $book->update_time=time();
                        $book->save();
                        $transaction->commit();
                    }catch ( Exception $e){
                        //事务回滚
                        $transaction->rollBack();
                    }

                    \Yii::$app->session->setFlash('success', '修改成功');
                    //跳转
                    return $this->redirect(['chapter/index','id'=>$model->book_id]);
                }
            }
            return $this->render('add', ['model' => $model]);
        }else{
            throw new ForbiddenHttpException('对不起,无可操作数据表');
        }

    }

    //章节删除
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $book_id=\Yii::$app->request->post('book_id');
        //分表
        $result=Chapter::resetPartitionIndex($book_id);
        if($result!=0){
            $chapter=Chapter::findOne(['id'=>$id]);
            $book=Book::findOne(['id'=>$chapter->book_id]);
            $file = BOOK_PATH.$chapter->path;
            $file_size=filesize($file);
            $book->size=$book->size-$file_size;
            $book->save();
            $res1=$chapter->delete();
            $res2=unlink($file);
            if($res1&&$res2){
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error';
        }

    }

    //ajax请求书名
    public function actionSearchTitle($q)
    {

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!$q) {
            return $out;
        }

        $data = Book::find()
            ->select('id, name as text')
            ->where(['is_api'=>0])
                ->andFilterWhere(['like', 'name', $q])
        ->limit(50)
        ->asArray()
        ->all();
                
    $out['results'] = array_values($data);
    return $out;
}

    //查看章节内容
    public function actionSelectContent($id,$book_id){
        $result=Chapter::resetPartitionIndex($book_id);
        if($result!=0){
            $data=Chapter::find()->select(['path','chapter_name'])->where(['id'=>$id])->one();
            $chapter_content=file_get_contents(BOOK_PATH.$data->path);
            return $this->render('content',['chapter_content'=>$chapter_content,'chapter_name'=>$data->chapter_name]);
        }else{
            throw new ForbiddenHttpException('对不起,无可操作数据表');
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