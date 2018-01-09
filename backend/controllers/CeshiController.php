<?php
namespace backend\controllers;
use backend\models\Author;
use backend\models\Book;
use backend\models\Chapter;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\UploadedFile;

class CeshiController extends Controller{
    //章节添加
    public function actionAdd(){
        $model=new Chapter();
        $model->scenario=Chapter::SCENARIO_Add;//指定当前场景为SCENARIO_Add
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            $model->file=UploadedFile::getInstance($model,'file');
            if($model->validate()){

                $dir=BOOK_PATH.date("Y").'/'.date('m').'/'.date('d').'/';
                if (!is_dir($dir)) {
                    mkdir($dir,0777,true);
                }
                $fileName=uniqid() . rand(1, 100000).'.'.$model->file->extension;//文件名
                $path=$dir.'/'.$fileName;//路径拼上文件名
                $model->file->saveAs($path,false);//移动文件
                /////////////////////////分割上传章节开始//////////////////////
                //$bookname=$dir; /*文件夹名字*/
                //$re /*入库id*/,
                $newName=$fileName; /*超大TXT小说*/


               // $dir="./".base64_encode($bookname);
                if(!file_exists($dir))
                {
                    mkdir($dir,0777,true);
                }
                $file_name=$path;

                $str=file_get_contents($file_name);

                //$str=mb_convert_encoding($str,"UTF-8","GBK");

                $arr=[];

                if(preg_match_all("/(\x{7b2c})(\s*)([\x{96f6}\x{4e00}\x{4e8c}\x{4e09}\x{56db}\x{4e94}\x{516d}\x{4e03}\x{516b}\x{4e5d}\x{5341}\x{767e}\x{5343}0-9]+)(\s*)([\x{7ae0}\x{8282}]+)/u",$str,$matches))
                {
                    $matches=array_slice($matches[0], 0,count($matches[0]));
                    //var_dump($matches);exit;

                    for ($i=0; $i <count($matches); $i++)
                    {
                        $j=$i+1;
                        if(isset($matches[$j]))
                        {
                            $pattern="#$matches[$i](.*)$matches[$j]#isU";

                            $arr[$i]=$pattern;
                        }
                        else
                        {
                            $offset=count($arr);

                            $arr[$offset]="#$matches[$i](.*)[\w]#isU";
                        }
                    }

                }

                $arr=array_unique($arr);

                foreach ($arr as $key => $value)
                {
                    preg_match($value, $str,$arr[$key]);
                    unset($arr[$key][0]);
                }
                // static $bookContent=[];
                // foreach ($arr as $key => $value)
                // {

                // if(isset($value[1]))
                // {
                // $chaptername =strstr($value[1], "\n", true);
                // }
                // else
                // {
                // $chaptername='哎呀没处理好';
                // }

                // @$bookContent[$matches[$key].$chaptername]=$value[1];
                // unset($bookContent[$key]);

                // }
                $i=1;
                foreach ($arr as $key => $value)
                {

                  //  $file_name=$dir.'/'.rand(10000,99999).'.txt';
                    $file_name=date("Y").'/'.date('m').'/'.date('d').'/'.uniqid() . rand(1, 100000).'.'.$model->file->extension;
                    file_put_contents($file_name, $value);
                    $model->path=$file_name;
                    $model->no=$i++;
                    $model->chapter_name=$matches[$key];
                    //保存所有数据
                    $model->create_time=time();
                    //DB::table('chapter')->insert(['book_id'=>$re,'chapter'=>$file_name,'chaptername'=>$matches[$key]]);

                }

                ////////////////////////分割上传章节结束/////////////////////

               // $bookPath=date("Y").'/'.date('m').'/'.date('d').'/'.$fileName;//数据库保存路径
              //  $model->path=$bookPath;
                //保存所有数据
               // $model->create_time=time();
                $transaction=\Yii::$app->db->beginTransaction();//开启事务
                try{
                    $model->save();
                    $book=Book::findOne(['id'=>$model->book_id]);
                    $redis=new \Redis();
                    $redis->connect('127.0.0.1');
                    $redis->set($model->book_id,$model->file->size);
                    $book->size=$book->size+$model->file->size;
                    $book->is_end=$model->is_end;
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
    }

    //批量插入版权方图书
    public function actionInsert(){
        $postUrl = 'http://partner.chuangbie.com/partner/booklist';
        $curlPost =['partner_id'=>2130,'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b','page_size'=>100];
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        $datas=json_decode($data,true);
        //var_dump($datas['content']['data']);exit;
        foreach ($datas['content']['data'] as $data){
            $relust=Book::findOne(['name'=>$data['book_name']]);
            if(!$relust){
                $author=Author::findOne(['name'=>$data['author_name']]);
                $author_id='';
                if($author){
                    $author_id=$author->id;
                }else{
                    $author2=new Author();
                    $author2->name=$data['author_name'];
                    $author2->create_time=time();
                    $author2->save(false);
                    $author_id=$author2->id;
                }
                $category_id='';

                if($data['ftype_id']==10){
                    $category_id=29;
                }elseif ($data['ftype_id']==157){
                    $category_id=36;
                }elseif($data['ftype_id']==0){
                    $category_id=38;
                }elseif ($data['ftype_id']==1){
                    $category_id=16;
                }elseif ($data['ftype_id']==2){
                    $category_id=22;
                }elseif ($data['ftype_id']==3){
                    $category_id=28;
                }elseif ($data['ftype_id']==4){
                    $category_id=19;
                }elseif ($data['ftype_id']==5){
                    $category_id=26;
                }elseif ($data['ftype_id']==6){
                    $category_id=18;
                }elseif ($data['ftype_id']==8){
                    $category_id=35;
                }elseif ($data['ftype_id']==9){
                    $category_id=34;
                }elseif ($data['ftype_id']==13){
                    $category_id=30;
                }elseif($data['ftype_id']==149){
                    $category_id=33;

                }
                \Yii::$app->db->createCommand()->batchInsert(Book::tableName(),
                    ['copyright_book_id','name','author_id','category_id','from','ascription','image','intro','is_free','no','size','type','is_end','clicks','score','collection','downloads','price','create_time'],
                    [
                        [$data['book_id'],$data['book_name'],$author_id,$category_id,3,4,$data['cover_url'],$data['description'],2,1,$data['word_count']*2,'txt',$data['status'],10000,8,2000,2000,5,time()],

                    ])->execute();
            }

        }
    }

    //检测爬虫书和版权书是否重复
    public function actionDetectionRepetition(){
        $postUrl = 'http://partner.chuangbie.com/partner/booklist';
        $curlPost =['partner_id'=>2130,'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b','page_size'=>100];
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        $datas=json_decode($data,true);
        foreach ($datas['content']['data'] as $data){
            $book_name=$data['book_name'];
            $models=\Yii::$app->db->createCommand("SELECT name FROM book WHERE name='$book_name' AND `from`=4")->queryScalar();
            var_dump($models);
        }
    }

    public function actionEdit(){
        $books=Book::find()->all();
        foreach ($books as $book){
            $book->image=HTTP_PATH.$book->image;
            $book->save();
        }
    }
}