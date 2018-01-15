<?php
namespace frontend\controllers;
use libs\PostRequest;
use libs\Verification;
use yii\web\Response;

class YuewenController extends \yii\web\Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //获取版权章节列表
    public function actionChapterList(){
        $result = [
            'flag'=>false,
        ];
        if(\Yii::$app->request->isPost){
            //验证
            $obj=new Verification();
            $res=$obj->check();
           //if($res){
              // $result['msg']= $res;
           // }else{

                //接收手机端传递参数
                $copyright_book_id=\Yii::$app->request->post('copyright_book_id');//版权书id
                $min_chapter_id =\Yii::$app->request->post('min_chapter_id ');//起始章节 ID 默认为 0(不含该章)
                $page_now=\Yii::$app->request->post('page_now');//分页显示时，为当前第几页，默认为 1
                $page_size=\Yii::$app->request->post('page_size');//分页显示时，为每页大小，默认全部显示
                //请求地址
                $postUrl = 'http://partner.chuangbie.com/partner/chapterlist';
                $curlPost =[
                    'partner_id'=>2130,
                    'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                    'book_id'=>$copyright_book_id,
                    'min_chapter_id '=>$min_chapter_id,
                    'page_now'=>$page_now,
                    'page_size'=>$page_size,
                ];

                $post=new PostRequest();
                $data=$post->request_post($postUrl,$curlPost);
                return json_decode($data);

          // }


        }else{
            $result['code']=400;
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //获取章节内容
    public function actionChapterContent(){
        $result = [
            'flag'=>false,
        ];
        if(\Yii::$app->request->isPost){
            //验证
            $obj=new Verification();
            $res=$obj->check();
           //if($res){
              //  $result['msg']= $res;
         //   }else{

                //接收手机端传递参数
                $copyright_book_id=\Yii::$app->request->post('copyright_book_id');//版权书id
                $copyright_chapter_ids=\Yii::$app->request->post('copyright_chapter_id');//版权书章节id
                //var_dump($copyright_chapter_ids);exit;
                //请求地址
                $postUrl = 'http://partner.chuangbie.com/partner/chaptercontent';
                //遍历获取多章节内容
                $datas=[];
                foreach ( $copyright_chapter_ids as  $copyright_chapter_id){
                    $curlPost =[
                        'partner_id'=>2130,
                        'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                        'book_id'=>$copyright_book_id,
                        'chapter_id'=>$copyright_chapter_id,
                    ];
                    $post=new PostRequest();
                    $datas[]=json_decode($post->request_post($postUrl,$curlPost));

                }
                return $datas;

          //  }

        }else{
            $result['code']=400;
            $result['msg']='请求方式错误';

        }
        return $result;
    }

}