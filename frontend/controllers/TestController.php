<?php
namespace frontend\controllers;
use backend\models\Author;
use backend\models\Book;
use backend\models\Chapter;
use backend\models\Reading;
use frontend\models\SmsDemo;
use libs\PostRequest;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\Controller;
//调试控制器
class TestController extends Controller
{
    public $token = 'yuekukuyue666888';

    public function actionSign()
    {

        $time=strtotime("2018-04-07");
        var_dump($time);exit;
        //var_dump(time());exit;
       // $p = ['category_id' =>34,'password'=>123456,'captcha'=>656618,'imei'=>86634103769185,'address'=>'四川省绵阳市'];
        //$p = ['time' =>103, 'user_id'=>40,'chapter_id'=>3152,'ticket'=>19,'voucher'=>0];
         $p=['category_id' =>34,'keyword'=>'我的','time'=>1521022964];
        //1.对key做升序排列 //['a'=>'','b'=>'','c'=>'','time'=>'']
        ksort($p);
        //2. 将参数拼接成字符串 a=4&b=123&c=77&time=12312312
        $s = urldecode(http_build_query($p));
        var_dump($s);exit;
        //3 将token拼接到字符串前面.然后做md5运算,将结果转换成大写
        $sign = strtoupper(md5($this->token . $s));
       /* $model1=new Book();
        $model1->name='西游记';
        $model1->author_id=2;
        $model1->category_id=16;
        $model1->from=4;
        $model1->ascription=2;
        $model1->image=123;
        $model1->intro=123;
        $model1->is_free=0;
        $model1->price=0;
        $model1->no=0;
        $model1->size=0;
        $model1->type='txt';
        $model1->clicks= rand(5000,10000);
        $model1->score= rand(7,10);
        $model1->collection=rand(5000,10000);
        $model1->downloads=rand(5000,10000);
        $model1->price=0;
        $model1->last_update_chapter_id=20;
        $model1->last_update_chapter_name=21;
        $model1->status=1;
        $model1->create_time=time();

        $transaction=\Yii::$app->db->beginTransaction();//开启事务
        try{
            $model1->save();
            $model2=new Chapter();
            $model2->book_id=$model1->id;
           // var_dump($model1->id);exit;
            $model2->no=0;
            $model2->chapter_name='西游记';
            $model2->word_count=0;
            $model2->path='www';
            $model2->is_free==0;
            $model2->create_time=time();
            $model2->save(false);
            $transaction->commit();
        }catch (Exception $e){
            //事务回滚
            $transaction->rollBack();
        }*/
        //var_dump($content);exit;
    }

    public function actionSms($phone)
    {
        $demo = new SmsDemo(
            "LTAIypgT6xAIPdMq",
            "tneztyzfbgbMVRB87TFKrBUhMv3HnM"
        );

        $captcha = rand(100000, 999999);
        echo "SmsDemo::sendSms\n";
        $response = $demo->sendSms(
            "阅cool书城", // 短信签名
            "SMS_117515881", // 短信模板编号
            "$phone", // 短信接收者
            Array(  // 短信模板中字段的值
                "code" => $captcha,
            ),
            "123"
        );
        print_r($response);

        echo "SmsDemo::queryDetails\n";
        $response = $demo->queryDetails(
            "12345678901",  // phoneNumbers 电话号码
            "20170718", // sendDate 发送时间
            10, // pageSize 分页大小
            1 // currentPage 当前页码
        // "abcd" // bizId 短信发送流水号，选填
        );
        print_r($response);
    }

    //插入17k章节内容
    public function actionSeventeenContent(){
        //设置脚本执行时间(不终止)
        set_time_limit(0);
        //查询数据库图书,获取17k书id
        $books=\Yii::$app->db->createCommand('SELECT id,copyright_book_id,name,size FROM book WHERE ascription=3 AND id<26687')->queryAll();
        $num=count($books);
        //var_dump($books);exit;
        for($i=0;$i<($num-1);$i++){
            //$Chapter=Chapter::findOne(['book_id'=>$books[$i]['id']]);
            $get=new PostRequest();
            $data=$get->send_request('http://api.17k.com/v2/book/'.$books[$i]['copyright_book_id'].'/volumes',

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
            foreach ($datas->data->volumes as $rows ){
                //var_dump($rows);exit;
                foreach ($rows->chapters as  $row){
                    //var_dump($row->id);exit;
                    $get2=new PostRequest();
                    $contents=$get2->send_request('http://api.17k.com/v2/book/'.$books[$i]['copyright_book_id'].'/chapter/'.$row->id.'/content',

                        [
                            '_access_version'=>2,
                            '_versions'=>958,
                            'access_token'=>'',
                            'app_key'=>2222420362,
                        ]
                    );
                    $contents=(json_decode($contents));
                    //将章节内容写入文件
                    $string= $contents->data->name ."\n" .$contents->data->content ."\n";
                    $fp = fopen($dir2 . '/' . $fileName2, 'a');
                    fwrite($fp, $string);
                    echo  iconv('utf-8','gbk','存入章节'. $contents->data->name."\n");
                }
            }
            fclose($fp);
            ////////////////////////存入数据库///////////////////////////////
            $model=new Chapter();
            $model->book_id=$books[$i]['id'];
            $model->no=0;
            $model->chapter_name=$books[$i]['name'];
            $model->word_count=$contents->data->word_count*2;
            $model->path=$uploadSuccessPath;
            $model->create_time=time();
            $transaction=\Yii::$app->db->beginTransaction();//开启事务
            try{
                $model->save();
                $model2=Book::findOne(['id'=>$books[$i]['id']]);
                $model2->ascription=2;
                $model2->save();
                echo  iconv('utf-8','gbk','保存章节内容成功');
            }catch (Exception $e){
                //事务回滚
                $transaction->rollBack();
                echo  iconv('utf-8','gbk','保存章节内容失败');
            }


        }

        /*foreach ($books as $book){
            $Chapter=Chapter::findOne(['book_id'=>$book[$i]['id']]);
            //判断这本书是否已上传章节内容
            //var_dump(111);exit;
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
                var_dump($datas->data->volumes[0]->code);*/
                //var_dump($datas->data->volumes);

                //var_dump($datas);exit;

                ////////////////////////////定义章节内容路径/////////////////////
               // $books_path=str_replace('\\','/',realpath(dirname(__FILE__).'/../../')).'/books/';
                //$dir2 = $books_path . date("Y") . '/' . date('m') . '/' . date('d') . '/';
               //if (!is_dir($dir2)) {
               //     mkdir($dir2, 0777, true);
               // }
              //  $fileName2 = uniqid() . rand(1, 100000) . '.' . 'txt';//文件名
               // $uploadSuccessPath = date("Y") . '/' . date("m") . '/' . date("d") . '/' . $fileName2;
                ///////////////////////定义章节内容路径结束//////////////////////////
               //var_dump($datas->data->volumes[1]->chapters);exit;
               //var_dump($datas['data']);exit;
                /*foreach ($datas->data->volumes[1]->chapters as $row){
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
                    //var_dump($contents->data->name);exit;

                    //将章节内容写入文件
                    $string= $contents->data->name ."\n" .$contents->data->content ."\n";
                    $fp = fopen($dir2 . '/' . $fileName2, 'a');
                    fwrite($fp, $string);
                    echo  iconv('utf-8','gbk','存入章节'. $contents->data->name."\n");

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
                    $model2->save();
                    echo  iconv('utf-8','gbk','保存章节内容成功');
                }catch (Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                    echo  iconv('utf-8','gbk','保存章节内容失败');
                }*///}

       // }

    }

    //插入17k基本图书信息
    public function actionSeventeenInfo()
    {
        //设置脚本执行时间(不终止)
        set_time_limit(0);
        ///////////////获取分类图书列表/////////////////
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
                    'page' => $i,
                    'site' => 2,
                    'sort_type' => 6
                ]
            );
            $datas = (json_decode($data));
            //var_dump($datas);exit;
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
                    //$img = file_get_contents($data->cover);
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
                                    4,
                                    3,
                                    $data->cover,
                                    $data->intro,
                                    0,
                                    0,
                                    $data->word_count*2,
                                    'txt',
                                    1,
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
                        //echo '存入图书:' . $data->title;
                        echo iconv('utf-8', 'gbk', '存入图书' . $data->book_name . "\n");
                    }

                }

            }
        }

    }

    //查看最近三天以前用户看的书
    public function actionSelectRead(){
        $time=strtotime('20180301');
        $models=Reading::find()->where(['>','create_time',$time])->all();
        foreach ($models as  $model){
            echo $model->user->tel."</br>";
        }

    }

    //删除数据库昨日插入无章节图书
    public function actionDelBook(){
        //查询数据库追书神器书id
        //$BookIds=Book::find()->select('id')->where(['ascription'=>5])->andWhere([''])->column();
        $BookIds=\Yii::$app->db->createCommand('SELECT id FROM book WHERE ascription=5 AND TO_DAYS(NOW()) - TO_DAYS(from_unixtime(create_time,\'%Y-%m-%d\')) = 1')->queryColumn();
        //$BookIds=\Yii::$app->db->createCommand('SELECT id FROM book WHERE ascription=5 AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= from_unixtime(create_time,\'%Y-%m-%d\')')->queryColumn();
        foreach ($BookIds as $bookId){
            $result=Chapter::resetPartitionIndex($bookId);
            if($result!=0){
                $ChapterId=Chapter::find()->select('id')->where(['book_id'=>$bookId])->scalar();
                if(!$ChapterId){
                    $Book=Book::find()->where(['id'=>$bookId])->one();
                    //echo '</br>'.$Book->name;
                    $author_id=$Book->author_id;//作者id
                    $path=$Book->image;
                    $book_name=$Book->name;
                    $ascription=$Book->ascription;
                    $transaction=\Yii::$app->db->beginTransaction();//开启事务
                    try{
                        //删除书
                        $Book->delete();
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
                        echo '</br>删除书----'.$book_name.'----来自于----'.$ascription;
                    }catch (Exception $e){
                        //事务回滚
                        $transaction->rollBack();
                    }
                }

            }else{
                echo '</br>数据路无可操作章节表';
            }
        }
    }

    //替换追书封面
    public function actionReplaceBooking($page){
        //查询追书神器图书
       // $page=\Yii::$app->request->get('page');
        $query=Book::find()->where(['ascription'=>5])->andWhere(['NOT',['copyright_book_id'=>null]]);
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
        $i=0;
        foreach ($books as $book) {
            $get = new PostRequest();
            $data = $get->send_request('http://api.zhuishushenqi.com/book/' . $book->copyright_book_id,

                [
                    '_access_version' => 2,
                    '_versions' => 958,
                    'access_token' => '',
                    'app_key' => 2222420362,
                ]
            );
            $datas = (json_decode($data));
            //var_dump($datas);
            $img_url = 'http://statics.zhuishushenqi.com' . $datas->cover;
            $path = $book->image;
            try {
                $img = file_get_contents($img_url);
            } catch (\Exception $exception) {
                $img = file_get_contents('http://image.voogaa.cn/2018/03/16/default.jpg');
            }

            $dir = UPLOAD_PATH . date("Y") . '/' . date("m") . '/' . date("d") . '/';
            $fileName = uniqid() . rand(1, 100000) . '.jpg';
            $uploadSuccessPath = date("Y") . '/' . date("m") . '/' . date("d") . '/' . $fileName;
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($dir . '/' . $fileName, $img);
            $book->image = $uploadSuccessPath;
            if ($book->save()) {
                if ($path) {
                $path = UPLOAD_PATH . $path;
                unlink($path);
                }
                echo ++$i.'替换书' . $book->name . '封面成功</br>';
            } else {
                echo ++$i.'替换失败</br>';
            }
        }
    }


    public function actionDiaoYong(){
        //设置脚本执行时间(不终止)
        set_time_limit(0);
        for ($i=1;$i<=4;$i++){
            $this->actionReplaceBooking($i);
            echo '<p style="color: red">第'.$i.'页</p>';
        }

    }




}