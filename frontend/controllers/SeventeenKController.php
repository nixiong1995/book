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
            'code'=>400,
            'msg'=>''
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
            $get=new PostRequest();
            $data=$get->send_request('http://api.17k.com/v2/book/'.$book->copyright_book_id.'/volumes',

                [
                    '_access_version'=>2,
                    '_versions'=>958,
                    'access_token'=>'',
                    'app_key'=>2222420362,
                ]
            );
            $datas=(json_decode($data));
            //return $datas->data->volumes;
            if($datas->data->volumes[0]->code==100){
                $relust['code']=200;
                $relust['msg']='请求目录信息成功';
                $relust['data']=$datas->data->volumes[1];

            }else{
                $relust['code']=200;
                $relust['msg']='请求目录信息成功';
                $relust['data']=$datas->data->volumes[0];
            }
            //return $datas->data->volumes[0]->code;
        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }


    //请求章节内容
    public function actionContent(){
        $relust=[
            'code'=>400,
            'msg'=>''
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
                $relust['code']=200;
                $relust['msg']='请求章节内容成功';
                $relust['data'][]=$contents->data;

                //var_dump($contents->data->content);exit;
            }
        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }
}