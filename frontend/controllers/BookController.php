<?php
namespace frontend\controllers;
use backend\models\Author;
use backend\models\Book;
use backend\models\Chapter;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;

//插入图书
class BookController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //插入基本信息
    public function actionInfo(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $copyright_book_id=\Yii::$app->request->post('copyright_book_id');//版权书id
            $book_name=\Yii::$app->request->post('book_name');//书名
            $author_name=\Yii::$app->request->post('author_name');//作者名
            $category_id=\Yii::$app->request->post('category_id');//分类id
            $status=\Yii::$app->request->post('status');//书状态:1连载,2完结
            $img_url=\Yii::$app->request->post('img_url');//图片路径
            $intro=\Yii::$app->request->post('intro');//书简介
            $size=\Yii::$app->request->post('size');//书大小
            $last_update_chapter_name=\Yii::$app->request->post('last_update_chapter_name');//最新章节名称

            if(empty($copyright_book_id) || empty($book_name) || empty($author_name) || empty($category_id) || empty($img_url) || empty($intro) || empty($size) || empty($last_update_chapter_name) || empty($status)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }

            $img = file_get_contents($img_url);
            $dir = UPLOAD_PATH .date("Y") . '/' . date("m") . '/' . date("d") . '/';
            $fileName = uniqid() . rand(1, 100000) . '.jpg';
            $uploadSuccessPath = date("Y") . '/' . date("m") . '/' . date("d") . '/' . $fileName;
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            //根据书名判断数据库是否有该书
            $book=Book::findOne(['name'=>$book_name]);
            if($book){
                /*//判断该书来源
                if($book->ascription==4 || $book->ascription==2){
                    //保存图片
                    file_put_contents($dir . '/' . $fileName, $img);
                    $book->copyright_book_id=$category_id;
                    $book->category_id=$category_id;
                    $book->from=5;
                    $book->intro=$intro;
                    $book->size=$size*2;
                    $book->is_end=$status;
                    $book->ascription=5;
                    $book->image=$uploadSuccessPath;
                    $book->last_update_chapter_name=$last_update_chapter_name;
                    if($book->save(false)){
                        $relust['code']=200;
                        $relust['msg']='修改17K图书成功';
                        $relust['book_id']=$book->id;
                    }else{
                        $relust['msg']='修改17k图书失败';
                    }
                }else{
                    $relust['msg']='已存在该书';
                }*/
                $relust['msg']='数据库已有该书';

            }else{
                file_put_contents($dir . '/' . $fileName, $img);
                $author =Author::findOne(['name' =>$author_name]);
                if ($author) {
                    $author_id = $author->id;
                } else {
                    $author2 = new Author();
                    $author2->name =$author_name;
                    $author2->create_time = time();
                    $author2->save(false);
                    $author_id = $author2->id;
                }
                $model=new Book();
                $model->copyright_book_id=$copyright_book_id;
                $model->name=$book_name;
                $model->author_id=$author_id;
                $model->category_id=$category_id;
                $model->from=3;
                $model->ascription=5;
                $model->image=$uploadSuccessPath;
                $model->intro=$intro;
                $model->is_free=0;
                $model->price=0;
                $model->no=0;
                $model->clicks= rand(5000, 10000);
                $model->size=$size*2;
                $model->type='txt';
                $model->score=rand(7, 10);
                $model->status=1;
                $model->is_end=$status;
                $model->create_time=time();
                $model->collection=rand(5000, 10000);
                $model->downloads=rand(5000, 10000);
                $model->last_update_chapter_name=$last_update_chapter_name;
                if($model->save(false)){
                    $relust['code']=200;
                    $relust['msg']='存书成功';
                    $relust['book_id']=$model->id;
                    $relust['book_name']=$model->name;
                }else{
                    $relust['msg']='存书失败';
                }

            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //插入章节目录
    public function actionChapter(){
        $relust=[
          'code'=>400,
          'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $book_id=\Yii::$app->request->post('book_id');
            $sort_id=\Yii::$app->request->post('sort_id');
            $chapter_name=\Yii::$app->request->post('chapter_name');
            $word_count=\Yii::$app->request->post('word_count');
            $content=\Yii::$app->request->post('content');
            $status=\Yii::$app->request->post('status');
            //判断是否传入指定参数
            if(empty($book_id) || empty($sort_id) || empty($chapter_name) || empty($word_count) || empty($content)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }

            //判断数据库是否有该图书
            $book=Book::findOne(['id'=>$book_id]);
            if($book){
                //判断数据库是否存在该章节
                $chapter=Chapter::find()->where(['book_id'=>$book_id])->andWhere(['no'=>$sort_id])->one();
                if($chapter){
                    $relust['msg']='已存在该章节';
                    return $relust;
                }

                if($book->ascription==5){
                    //将章节内容写入文件保存
                    $dir2 = BOOK_PATH . date("Y") . '/' . date('m') . '/' . date('d') . '/';
                    if (!is_dir($dir2)) {
                        mkdir($dir2, 0777, true);
                    }
                    $fileName2 = uniqid() . rand(1, 100000) . '.' . 'txt';//文件名
                    $uploadSuccessPath = date("Y") . '/' . date("m") . '/' . date("d") . '/' . $fileName2;
                    file_put_contents($dir2 . '/' . $fileName2, $content);
                    $model=new Chapter();
                    $model->book_id=$book_id;
                    $model->no=$sort_id;
                    $model->chapter_name=$chapter_name;
                    $model->word_count=$word_count;
                    $model->path=$uploadSuccessPath;
                    $model->is_free=0;
                    $model->create_time=time();
                    if($model->save(false)){
                        $relust['code']=200;
                        $relust['msg']='成功存入章节'.$model->chapter_name;
                        $relust['sort_id']=$sort_id;
                    }else{
                        $relust['msg']='存入章节失败';
                    }
                    if($status){
                        $book->last_update_chapter_id=$model->id;
                        //$book->last_update_chapter_name=$model->chapter_name;
                        $book->save(false);
                    }

                }else{
                    $relust['msg']='该书不是分章节存取图书';
                }

            }else{
                $relust['msg']='数据库无该图书';
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //删除没有章节的图书
    public function actionDelBook(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //查询ascription=5的图书id
            $bookIds=\Yii::$app->db->createCommand('SELECT id FROM book WHERE ascription=5')->queryColumn();
            if($bookIds){
                //遍历查询图书是否存在章节信息
                foreach ($bookIds as $bookId){
                    $chapter=Chapter::findOne(['book_id'=>$bookId]);
                    //如果不存在就删除该图书
                    if(!$chapter){
                        $book=Book::find()->where(['id'=>$bookId])->one();
                        $author_id=$book->author_id;//作者id
                        $path=$book->image;
                        $transaction=\Yii::$app->db->beginTransaction();//开启事务
                        try{
                            $book->delete();
                            //删除图书
                            if($path){
                                $path=UPLOAD_PATH.$path;
                                unlink($path);
                            }

                            //删除作者(判断该作者是否还有其他书籍)
                            $res=Book::findOne(['author_id'=>$author_id]);
                            //该作者没有其他图书,删除该作者
                            if(!$res) {
                                $author = Author::findOne(['id' => $author_id]);
                                //var_dump($author);exit;
                                $author->delete();
                            }
                            $relust['code']=200;
                            $relust['msg']='删除空章节图书成功';
                            $transaction->commit();

                        }catch (Exception $e){
                            //事务回滚
                            $relust['msg']='删除空章节图书失败';
                            $transaction->rollBack();
                        }

                    }else{
                        $relust['code']=200;
                        $relust['msg']='无空章节图书';
                    }
                }

            }else{
                $relust['msg']='没有api归属图书';

            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }
}