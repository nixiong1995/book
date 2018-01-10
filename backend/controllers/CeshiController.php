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


    //检测版权方是否存在相同图书
    public function actionDetectionCopyright($id){

    }

}