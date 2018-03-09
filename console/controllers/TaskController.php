<?php
namespace console\controllers;
use backend\models\Author;
use backend\models\Book;
use backend\models\Chapter;
use backend\models\Recharge;
use Codeception\Exception\InjectionException;
use libs\PostRequest;
use yii\base\InvalidParamException;
use yii\console\Controller;
use yii\db\Exception;

class TaskController extends Controller{
    //手动清理超时未支付订单(24小时)
    public function actionClean(){
        //设置脚本执行时间(不终止)
        //set_time_limit(0);
        //当前时间 - 创建时间 > 24小时   ---> 创建时间 <  当前时间 - 24小时
        //超时未支付订单
        //sql: update order set status=0 where status = 1 and create_time < time()-24*3600
        //while (true){
            //Recharge::deleteAll('status=1 and create_time < '.(time()-60));
            Recharge::deleteAll('create_time < :create_time AND status = :status', [':create_time' =>(time()-180) , ':status' => '1']);
            //每隔一秒执行一次
            //sleep(1);
            echo '清理完成'.date('Y-m-d H:i:s')."\n";
       // }

    }

    //更新凯兴版权书数据
    public function actionUpdateCopyright(){
        $postUrl = 'http://partner.chuangbie.com/partner/booklist';
        $curlPost =['partner_id'=>2130,'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b','page_size'=>100];
        $post=new PostRequest();
        $data=$post->request_post($postUrl,$curlPost);
        $datas=json_decode($data,true);
        foreach ($datas['content']['data'] as $data){
            Book::updateAll(
                ['size'=>$data['word_count']*2,'is_end'=>$data['status'],'last_update_chapter_id'=>$data['last_update_chapter_id'],'last_update_chapter_name'=>$data['last_update_chapter_name'],'update_time'=>time()],
                ['name'=>$data['book_name']]);

        }

        echo '批量更新成功'.date('Y-m-d H:i:s')."\n";

    }

    //爬追书神器存入数据库
    /*public function actionZhuishuInsert(){
        //设置脚本执行时间(不终止)
        set_time_limit(0);
        ///////////////获取分类图书列表/////////////////
        $get=new PostRequest();
        $data=$get->send_request('api.zhuishushenqi.com/book/by-categories',
            [
                'major'=>'玄幻',
                'start'=>11,
                'limit'=>10,
                'gender'=>'male',
                'type'=>'hot',
            ]
        );
        $datas=(json_decode($data));
        //var_dump($datas->books);exit;
        //////////////////获取分类图书列表结束/////////////

        //循环判断数据库是否有该书
        foreach ($datas->books as $data) {
            //var_dump($data->title);exit;
            $book = Book::findOne(['name' => $data->title]);
            //var_dump($data->latelyFollower);exit;
            //判断是否有该书
            if (!$book) {
                //抓取图片
                $img_url = urldecode($data->cover);
                $img_url = str_replace('/agent/', '', $img_url);
                $res = strpos($img_url, 'http:');
                //判断该书url是否正确
                if ($res === false) {
                    //url不正确抓取没有没封面图片
                    $img = file_get_contents('http://image.voogaa.cn/2017/12/26/5a421e67933d55955.jpg');
                } else {
                    //url正确抓取该图片
                    $img = file_get_contents($img_url);
                }

                //图片存放路径
                //$dir =UPLOAD_PATH .date("Y").'/'.date("m").'/'.date("d").'/';
                $uplaods_path=str_replace('\\','/',realpath(dirname(__FILE__).'/../../')).'/uploads/';
                $dir = $uplaods_path . date("Y") . '/' . date("m") . '/' . date("d") . '/';
                $fileName = uniqid() . rand(1, 100000) . '.jpg';
                $uploadSuccessPath = date("Y") . '/' . date("m") . '/' . date("d") . '/' . $fileName;
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                //保存图片
                file_put_contents($dir . '/' . $fileName, $img);


                //========================获取章节列表==================================
                $get3 = new PostRequest();
                $chapters = $get3->send_request('http://api.zhuishushenqi.com/mix-toc/' . $data->_id,
                    [
                        'view' => 'summary',
                        'book' => $data->_id,
                    ]);
                $chapters = json_decode($chapters);

                //=====================获取章节列表结束=======================

                ////////////////////////////定义章节内容路径/////////////////////
                $books_path=str_replace('\\','/',realpath(dirname(__FILE__).'/../../')).'/books/';
                $dir2 = $books_path . date("Y") . '/' . date('m') . '/' . date('d') . '/';
                if (!is_dir($dir2)) {
                    mkdir($dir2, 0777, true);
                }
                $fileName2 = uniqid() . rand(1, 100000) . '.' . 'txt';//文件名
                $uploadSuccessPath2 = date("Y") . '/' . date("m") . '/' . date("d") . '/' . $fileName2;
                ///////////////////////定义章节内容路径结束//////////////////////////


                foreach ($chapters->mixToc->chapters as $chapter) {
                    //var_dump($chapter->title);exit;
                    $get4 = new PostRequest();
                    $content = $get4->send_request('http://chapterup.zhuishushenqi.com/chapter/' . urlencode($chapter->link),
                        [
                            'view' => 'summary',
                            'book' => $data->_id,
                        ]);
                    $content = json_decode($content);
                    //var_dump($content->chapter->body);exit;
                    //将章节内容写入文件
                    $string= $chapter->title ."\n" .$content->chapter->body ."\n";
                    $fp = fopen($dir2 . '/' . $fileName2, 'a');
                    fwrite($fp, $string);

                    echo  iconv('utf-8','gbk','存入章节'. $chapter->title."\n");
                }

                $fp = fopen($dir2 . '/' . $fileName2, 'a');
                fwrite($fp, $string);
                fclose($fp);
                $file_size=filesize($dir2 . '/' . $fileName2);
                //var_dump(111);exit;

                //======================获取章节列表结束=================================

                ////////////////////////存入数据库///////////////////////////////

                //判断是否有该作者
                $author=Author::findOne(['name'=>$data->author]);
                $author_id='';
                if($author){
                    $author_id=$author->id;
                }else{
                    $author2=new Author();
                    $author2->name=$data->author;
                    $author2->create_time=time();
                    $author2->save(false);
                    $author_id=$author2->id;
                }

                $model1=new Book();
                $model1->name=$data->title;
                $model1->author_id=$author_id;
                $model1->category_id=16;
                $model1->from=4;
                $model1->ascription=2;
                $model1->image=$uploadSuccessPath;
                $model1->intro=$data->shortIntro;
                $model1->is_free=0;
                $model1->price=0;
                $model1->no=0;
                $model1->size=$file_size;
                $model1->type='txt';
                $model1->clicks= rand(5000,10000);
                $model1->score= rand(7,10);
                $model1->collection=rand(5000,10000);
                $model1->downloads=rand(5000,10000);
                $model1->price=0;
                $model1->last_update_chapter_id=$data->latelyFollower;
                $model1->last_update_chapter_name=$data->lastChapter;
                $model1->status=1;
                $model1->create_time=time();

                $transaction=\Yii::$app->db->beginTransaction();//开启事务
                try{
                    $model1->save();
                    $model2=new Chapter();
                    $model2->book_id=$model1->id;
                    $model2->no=0;
                    $model2->chapter_name=$data->title;
                    $model2->word_count=0;
                    $model2->path=$uploadSuccessPath2;
                    $model2->is_free=0;
                    $model2->create_time=time();
                    $model2->save();
                    $transaction->commit();
                }catch (Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                }
                echo  iconv('utf-8','gbk','已存入图书'. $data->title."\n");
            }else{

            }
            echo  iconv('utf-8','gbk','已存在图书'. $data->title."\n");
        }
    }*/
    //插入17k章节内容
    public function actionZhuishuInsert(){
        //设置脚本执行时间(不终止)
        set_time_limit(0);
        //查询数据库图书,获取17k书id
        $books=\Yii::$app->db->createCommand('SELECT id,copyright_book_id,name,size FROM book WHERE ascription=3 limit 50')->queryAll();
        //var_dump($books);exit;
        foreach ($books as $book){
            $Chapter=Chapter::findOne(['book_id'=>$book['copyright_book_id']]);
            //判断这本书是否已上传章节内容
            if(!$Chapter){
                //没有上传章节内容,请求接口获取章节内容,并上传
                $get=new PostRequest();
                $data=$get->send_request('http://api.17k.com/v2/book/'.$book['copyright_book_id'].'/volumes',

                    [
                        '_access_version'=>2,
                        '_versions'=>958,
                        'access_token'=>'',
                        'app_key'=>2222420362,
                    ]
                );
                $datas=(json_decode($data));

                ////////////////////////////定义章节内容路径/////////////////////
                $books_path=str_replace('\\','/',realpath(dirname(__FILE__).'/../../')).'/books/';
                $dir2 = $books_path . date("Y") . '/' . date('m') . '/' . date('d') . '/';
                if (!is_dir($dir2)) {
                    mkdir($dir2, 0777, true);
                }
                $fileName2 = uniqid() . rand(1, 100000) . '.' . 'txt';//文件名
                $uploadSuccessPath = date("Y") . '/' . date("m") . '/' . date("d") . '/' . $fileName2;
                ///////////////////////定义章节内容路径结束//////////////////////////
                //var_dump($datas->data->volumes->chapters);exit;
                foreach ($datas->data->volumes[1]->chapters as $row){
                    //请求章节内容
                    //var_dump($row->id);exit;
                    $get=new PostRequest();
                    $contents=$get->send_request('http://api.17k.com/v2/book/'.$book['copyright_book_id'].'/chapter/'.$row->id.'/content',

                        [
                            '_access_version'=>2,
                            '_versions'=>958,
                            'access_token'=>'',
                            'app_key'=>2222420362,
                        ]
                    );
                    $contents=(json_decode($contents));
                   try{
                       $string= $contents->data->name ."\n" .$contents->data->content ."\n";
                       $fp = fopen($dir2 . '/' . $fileName2, 'a');
                       fwrite($fp, $string);
                       echo  iconv('utf-8','gbk','存入章节'. $contents->data->name."\n");
                   }catch (\Exception $e){
                       var_dump($contents);
                   }
                    //将章节内容写入文件



                }
                fclose($fp);
                ////////////////////////存入数据库///////////////////////////////
                $model=new Chapter();
                $model->book_id=$book['id'];
                $model->no=0;
                $model->chapter_name=$book['name'];
                $model->word_count=$book['size']/2;
                $model->path=$uploadSuccessPath;
                $model->create_time=time();
                $transaction=\Yii::$app->db->beginTransaction();//开启事务
                try{
                    $model->save();
                    $model2=Book::findOne(['id'=>$book['id']]);
                    $model2->ascription=2;
                    $model2->save(false);
                    echo  iconv('utf-8','gbk','保存章节内容成功');
                }catch (Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                    echo  iconv('utf-8','gbk','保存章节内容失败');
                }
            }

        }
    }

    //插入追书神器基本图书信息
    public function actionZhuishuInfo(){
        //设置脚本执行时间(不终止)
        set_time_limit(0);
        ///////////////获取分类图书列表/////////////////

        ////////////////////请求追书神器基本信息接口///////////////////////////////////////
        for($i=1;$i<=100;$i++){
            $get = new PostRequest();
            ////////////////////请求追书神器基本信息接口///////////////////////////////////////
            $data = $get->send_request('http://api.17k.com/v2/book?',
                [
                    '_access_version' => 2,
                    '_versions' => 958,
                    'app_key' => 2222420362,
                    'book_free' => 1,
                    'book_status' => 0,
                    'category_1' => 21,
                    'num' => 20,
                    'page' =>$i,
                    'site' => 2,
                    'sort_type' =>6
                ]
            );
            $datas=(json_decode($data));
            try{
                if($datas){
                    ////////////////////////////请求追书神器图书基本信息接口结束////////////////////
                    //遍历返回图书基本信息
                    foreach ($datas->data as $data) {
                        //查找数据库是否存在该书
                        $book = Book::findOne(['name' => $data->book_name]);
                        //var_dump($data);exit;
                        //判断是否有该书
                        if (!$book) {
                            //抓取图片
                            //$img_url = urldecode($data->cover);
                            //var_dump(11);exit;
                            //$img_url = str_replace('/agent/', '', $img_url);

                            //  $res = strpos($img_url, 'http:');
                            //判断该书url是否正确
                            // if ($res === false) {
                            //url不正确抓取没有没封面图片
                            //$img = file_get_contents('http://image.voogaa.cn/2017/12/26/5a421e67933d55955.jpg');
                            //  } else {
                            //url正确抓取该图片
                           // $img = file_get_contents($data->cover);
                            //}

                            //图片存放路径
                           /* $uplaods_path = str_replace('\\', '/', realpath(dirname(__FILE__) . '/../../')) . '/uploads/';
                            $dir = $uplaods_path . date("Y") . '/' . date("m") . '/' . date("d") . '/';
                            $fileName = uniqid() . rand(1, 100000) . '.jpg';
                            $uploadSuccessPath = date("Y") . '/' . date("m") . '/' . date("d") . '/' . $fileName;
                            if (!is_dir($dir)) {
                                mkdir($dir, 0777, true);
                            }
                            //保存图片
                            file_put_contents($dir . '/' . $fileName, $img);*/

                            ////////////////////////存入数据库///////////////////////////////

                            if($data->word_count>1000000){
                                //判断是否有该作者
                                $author = Author::findOne(['name' => $data->author_name]);
                                //$author_id='';
                                if ($author) {
                                    $author_id = $author->id;
                                } else {
                                    $author2 = new Author();
                                    $author2->name = $data->author_name;
                                    $author2->create_time = time();
                                    $author2->save(false);
                                    $author_id = $author2->id;
                                }

                                \Yii::$app->db->createCommand()->batchInsert(Book::tableName(),
                                    [
                                        'copyright_book_id',
                                        'name',
                                        'author_id',
                                        'category_id',
                                        'from',
                                        'ascription',
                                        'image',
                                        'intro',
                                        'is_free',
                                        'no',
                                        'size',
                                        'type',
                                        'is_end',
                                        'clicks',
                                        'score',
                                        'collection',
                                        'downloads',
                                        'price',
                                        'last_update_chapter_id',
                                        'last_update_chapter_name',
                                        'status',
                                        'create_time'
                                    ],
                                    [
                                        [
                                            $data->book_id,
                                            $data->book_name,
                                            $author_id,
                                            16,
                                            3,
                                            3,
                                            $data->cover,
                                            $data->intro,
                                            0,
                                            0,
                                            $data->word_count*2,
                                            'txt',
                                            2,
                                            rand(5000, 10000),
                                            rand(7, 10),
                                            rand(5000, 10000),
                                            rand(5000, 10000),
                                            0,
                                            $data->last_update_chapter_id,
                                            $data->last_update_chapter_name,
                                            1,
                                            time()
                                        ],
                                    ])->execute();

                                echo  iconv('utf-8','gbk',$i.'存入图书'. $data->book_name."\n");

                            }else{
                                echo  iconv('utf-8','gbk',$i.'已存在图书'. $data->book_name."\n");
                            }
                            //echo '存入图书:' . $data->title;
                            }


                    }
                }else {
                    echo iconv('utf-8', 'gbk', $i . '没有数据');
                }

            }catch (\Exception $e){
                var_dump($datas);
                exit;
            }


        }




    }

}