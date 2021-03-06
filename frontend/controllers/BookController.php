<?php
namespace frontend\controllers;
use backend\models\Author;
use backend\models\Book;
use backend\models\Chapter;
use backend\models\Uuid;
use DeepCopy\f004\UnclonableItem;
use yii\data\Pagination;
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
            try{
                $opts = array(
                    'http'=>array(
                        'method'=>"GET",
                        'timeout'=>1,//单位秒
                    )
                );

                $img=file_get_contents($img_url, false, stream_context_create($opts));
            }catch (\Exception $exception){
                $img =file_get_contents('http://image.voogaa.cn/2018/03/16/default.jpg');
            }
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
                $model->clicks=rand(5000, 10000);
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

    //更新图书
    public function actionUpdateBook()
    {
        $result = [
            'code' => 400,
            'msg' => '请求失败',
        ];
        if (\Yii::$app->request->isGet) {
            $page=\Yii::$app->request->get('page');
            $query=Book::find()->where(['ascription'=>5]);
            $count=ceil($query->count()/10);
            if($page>$count){
                $result['msg']='没有更多了';
                return $result;
            }
            $pager=new Pagination([
                'totalCount'=>$query->count(),//总条数
                'defaultPageSize'=>10,//每页显示条数
            ]);
            $books=$query->limit($pager->limit)->offset($pager->offset)->all();
            if($books){
                foreach ($books as $book){
                    $path=$book->image;
                    $author_id=$book->author_id;//作者id
                    $res=Chapter::resetPartitionIndex($book->id);
                    if($res!=0){
                        $last_chapter=Chapter::find()->where(['book_id'=>$book->id])->orderBy('no DESC')->one();
                        if($last_chapter){
                            $result['code']=200;
                            $result['msg']='成功返回信息';
                            $result['data'][]=[
                                'book_id'=>$book->id,
                                'copyright_book_id'=>$book->copyright_book_id,
                                'book_name'=>$book->name,
                                'author_name'=>$book->author->name,
                                'max_sort_id'=>$last_chapter->no,
                                'last_chapter_name'=>$last_chapter->chapter_name,
                            ];
                        }else{
                            $transaction=\Yii::$app->db->beginTransaction();//开启事务
                            try{
                                //删除书
                                $book->delete();
                                if($path){
                                    $path=UPLOAD_PATH.$path;
                                    unlink($path);
                                }

                                //删除作者(判断该作者是否还有其他书籍)
                                $re=Book::findOne(['author_id'=>$author_id]);
                                if(!$re){
                                    $author=Author::findOne(['id'=>$author_id]);
                                    //作者照片
                                    $path3=$author->image;
                                    $author->delete();
                                    if($path3){
                                        $path3=UPLOAD_PATH.$path3;
                                        unlink($path3);
                                    }
                                }
                                $transaction->commit();
                            }catch (Exception $e){
                                //事务回滚
                                $transaction->rollBack();
                            }
                            $result['msg']=201;
                            $result['msg']='该书无章节已删除';
                        }

                    }else {
                        $result['msg'] = '无可操作数据表';
                    }
                }

            }else{
                $result['msg']='没有数据';

            }

        } else {
            $result['msg']='请求方式错误';
        }
        return $result;
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
            if(empty($book_id) || empty($sort_id) ||  empty($word_count) || empty($content)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }

            if(empty($chapter_name)){
                $chapter_name='第'.$sort_id.'章';
            }

            //判断数据库是否有该图书
            $book=Book::findOne(['id'=>$book_id]);
            if($book){
                //判断数据库是否存在该章节
                //$chapter=Chapter::find()->where(['book_id'=>$book_id])->andWhere(['no'=>$sort_id])->one();
                $res=Chapter::resetPartitionIndex($book_id);
                if($res!=0){

                   // $chapter=\Yii::$app->db->createCommand("SELECT id FROM chapter WHERE `book_id`=$book_id AND `no`=$sort_id")->queryScalar();
                    $chapter=Chapter::find()->select('id')->where(['book_id'=>$book_id])->andWhere(['no'=>$sort_id])->scalar();
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
                        $Uuid=new Uuid();
                        $Uuid->name='chapter'.$res;
                        $Uuid->save();
                        $model=new Chapter();
                        $model->id=$Uuid->id;
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
                            $book->update_time=time();
                            $book->last_update_chapter_id=$model->id;
                            $book->last_update_chapter_name=$model->chapter_name;
                            $book->save(false);
                        }

                    }else{
                        $relust['msg']='该书不是分章节存取图书';
                    }
                }else{
                    $relust['msg']='数据库无可操作数据表';
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
            //接收图书id
            $book_id=\Yii::$app->request->post('book_id');
            if(empty($book_id)){
                $relust['msg']='未传入指定参数';
            }
            //分表查询
            $result=Chapter::resetPartitionIndex($book_id);
            if($result!=0){

                $chapter=Chapter::findOne(['book_id'=>$book_id]);
                //如果不存在就删除该图书
                if(!$chapter){
                    $book=Book::find()->where(['id'=>$book_id])->one();
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

                }else {
                    $relust['code'] = 200;
                    $relust['msg'] = '该书无空章节';
                }
            }else{
                $relust['msg']='无可操作数据表';
            }





        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //传入page返回图书id,书名,作者名
    public function actionObtainBookinfo(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isGet){
            //接收参数
            $page=\Yii::$app->request->get('page');
            $query=Book::find()->where(['ascription'=>5]);
            $count=ceil($query->count()/50);
            if($page>$count){
                $result['msg']='没有更多了';
                return $result;
            }
            $pager=new Pagination([
                'totalCount'=>$query->count(),//总条数
                'defaultPageSize'=>50,//每页显示条数
            ]);
            $books=$query->limit($pager->limit)->offset($pager->offset)->all();
            if($books){
                $result['code']=200;
                $result['msg']='成功返回信息';
                foreach ($books as $book){
                    $result['data'][]=
                        [
                            'book_id'=>$book->id,
                            'book_name'=>$book->name,
                            'author_name'=>$book->author->name
                        ];
                }
            }else{
                $result['code']=404;
                $result['msg']='暂无数据';
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //替换书图片
    public function actionReplaceBooking(){
        $result=[
            'code'=>200,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $book_id=\Yii::$app->request->post('book_id');
            $img_url=\Yii::$app->request->post('img_url');
            if(empty($book_id) || empty($img_url)){
                $result['msg']='未传入指定参数';
                return $result;
            }

            $book=Book::find()->where(['id'=>$book_id])->one();

            if($book){
                if($book->ascription!=5){
                    $result['msg']='该书不属于追书神器';
                    return $result;
                }

                $path=$book->image;
                try{
                    $opts = array(
                        'http'=>array(
                            'method'=>"GET",
                            'timeout'=>1,//单位秒
                        )
                    );

                   $img=file_get_contents($img_url, false, stream_context_create($opts));
                }catch (\Exception $exception){
                    $img =file_get_contents('http://image.voogaa.cn/2018/03/16/default.jpg');
                }

                $dir = UPLOAD_PATH .date("Y") . '/' . date("m") . '/' . date("d") . '/';
                $fileName = uniqid() . rand(1, 100000) . '.jpg';
                $uploadSuccessPath = date("Y") . '/' . date("m") . '/' . date("d") . '/' . $fileName;
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($dir . '/' . $fileName, $img);
                $book->image=$uploadSuccessPath;
                if($book->save()){
                    if($path){
                        $path=UPLOAD_PATH.$path;
                        unlink($path);
                    }
                    $result['code']=200;
                    $result['msg']='替换成功';
                }else{
                    $result['msg']='替换失败';
                }

            }else{
                $result['code']=404;
                $result['msg']='未找到该书';
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}