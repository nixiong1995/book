<?php
//请求17k小说接口
namespace frontend\controllers;
use backend\models\Book;
use libs\PostRequest;
use yii\web\Controller;
use yii\web\Response;

class SeventeenKController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //请求章节目录
    public function actionCatalog(){
        $relust=[
            'flag'=>false,
            'content'=>[
                "totalcount"=>0,
                'totalpage'=>0,
            ]

        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $book_id=\Yii::$app->request->post('book_id');
            if(empty($book_id)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            $book=Book::findOne(['id'=>$book_id]);
            //请求接口
            $get = new PostRequest();
            ////////////////////请求追书神器基本信息接口///////////////////////////////////////
            $info = $get->send_request('http://api.zhuishushenqi.com/mix-atoc/'.$book->copyright_book_id,
                [
                    'gender'=>'male',
                    'type'=>'hot',
                    'major'=>'玄幻',
                    'minor'=>'',
                    'start'=>133,
                    'limit'=>2,
                ]
            );
            $infos = (json_decode($info));
            var_dump($infos);exit;
           return $datas->data->volumes;
            //var_dump($datas->data->volumes);exit;
           /* foreach ($datas->data->volumes as $rows){
                foreach ($rows->chapters as $row){
                    //var_dump($row->name);
                    $relust['flag']=true;
                   $relust['content']['data'][]=
                   [
                       'chapter_id'=>$row->id,
                       'chapter_name'=>$row->name,
                       'book_id'=>$book_id,
                       'vname'=>'第一卷',
                       'volume_id'=>$row->volume_id,
                       'is_vip'=>0,
                       'sortid'=>$row->id,
                       'word_count'=>$row->word_count,
                       'update_time'=>$row->updated_at,
                       'no'=>0
                   ];
               $relust['msg']='成功返回章节信息';
               }
            }*/

           // return $datas->data->volumes;

            //return $datas->data->volumes[0]->code;
        }//else{
           // $relust['msg']='请求方式错误';
      // }
       // return $relust;
    }


    //请求章节内容
    public function actionContent(){
        $relust=[
            'flag'=>false,
            'content'=>[],
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $book_id=\Yii::$app->request->post('book_id');
            $chapter_id=\Yii::$app->request->post('chapter_id');//数组
            if(empty($book_id) || empty($chapter_id)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            $chapter_ids=explode(',',$chapter_id);
            $chapter_ids=array_filter($chapter_ids);
            $book=Book::findOne(['id'=>$book_id]);
            $datas=[];
            foreach ($chapter_ids as  $chapter_id){
                $get=new PostRequest();
                $contents=$get->send_request('http://api.17k.com/v2/book/'.$book->copyright_book_id.'/chapter/'.$chapter_id.'/content',

                    [
                        '_access_version'=>2,
                        '_versions'=>958,
                        'access_token'=>'',
                        'app_key'=>2222420362,
                    ]
                );
                $contents=(json_decode($contents));
                //var_dump($contents->data->content);exit;

                $relust['flag']=true;
                $relust['content']['data']['chapter_content']=$contents->data->content;
                $relust['msg']='成功返回章节内容';
                $datas[]=$relust;
                //var_dump($contents->data->content);exit;
            }
            return $datas;
        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }
}