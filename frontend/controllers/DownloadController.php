<?php
namespace frontend\controllers;
use backend\models\Chapter;
use libs\Read;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

class DownloadController extends Controller{
    public $enableCsrfValidation=false;
    public $token = 'yuekukuyue666888';

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionDownload(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[],
        ];
        if(\Yii::$app->request->isGet){
           // $obj=new Verification();
            //$res=$obj->check();
            //if($res){
              //  $result['msg']= $res;
           // }else{
                $book_id=\Yii::$app->request->get('book_id');
                $no=\Yii::$app->request->get('no');
                $nos=explode(',',$no);
                if($book_id!=null && $no!=null){
                    $datas=[];

                    foreach ($nos as $no){
                        $chapters=Chapter::find()->where(['book_id'=>$book_id,'no'=>$no])->all();
                        foreach ($chapters as $chapter){
                            $datas[$chapter->no]=BOOK_PATH.$chapter->path;
                        }
                    }
                    foreach ($datas as $data){
                        $exts = get_loaded_extensions();
                        $mimeType = 'application/octet-stream';
                        if(array_search('fileinfo', $exts)===FALSE)
                        {
                            $sizeInfo = getimagesize($data);
                            $mimeType = $sizeInfo['mime'];
                        }else{
                            $mimeType = mime_content_type($data);
                        }
                        $Read=new Read();
                        $Read->smartReadFile($data,$mimeType);
                    }



                }
           // }


        }else{
            $result['msg']='请求方式错误';
        }
        return $result;

    }
}