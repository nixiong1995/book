<?php
namespace frontend\controllers;
use backend\models\App;
use backend\models\Book;
use backend\models\Chapter;
use libs\Read;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

class DownloadController extends Controller
{
    public $enableCsrfValidation = false;
    public $token = 'yuekukuyue666888';

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //小说下载
    public function actionDownload()
    {
        $result = [
            'code' => 400,
            'msg' => '',//错误信息,如果有
        ];
        if (\Yii::$app->request->isGet) {
             $obj=new Verification();
            $res=$obj->check();
         //  if($res){
           //  $result['msg']= $res;
          //  }else{
            $book_id = \Yii::$app->request->get('book_id');
            $no = \Yii::$app->request->get('no');
            if ($book_id != null && $no != null) {
                $file = \Yii::$app->db->createCommand("SELECT path FROM chapter WHERE book_id=$book_id AND no=$no")->queryScalar();
                if($file){
                    //var_dump($file);exit;
                    $file = BOOK_PATH . $file;//加上完整路径
                    $exts = get_loaded_extensions();
                    $mimeType = 'application/octet-stream';
                    if (array_search('fileinfo', $exts) === FALSE) {
                        $sizeInfo = getimagesize($file);
                        $mimeType = $sizeInfo['mime'];
                    } else {
                        $mimeType = mime_content_type($file);
                    }
                    $Read = new Read();
                    //var_dump(HTTP_PATH.$path);exit;
                    $Read->smartReadFile($file, $mimeType);
                    //判断章节号是否小于2,如果小于说明是第一次下载,该书下载次数加1
                    if($no<2){
                        $model=Book::findOne(['id'=>$book_id]);
                        $model->downloads=$model->downloads+1;
                        $model->save();
                    }
                    $result['code']=200;
                    $result['msg']='下载成功';
                }else{
                    $result['msg']='没有该下载文件';
                }

            }else{
                $result['msg']='缺少下载参数';
            }
           // }
            }else {
                $result['msg'] = '请求方式错误';
            }
            return $result;
        }


    //app下载
    public function actionAppDownload(){
        $model=App::find()->orderBy('create_time DESC')->one();
        $file = \Yii::getAlias('@backend').'/web/'.$model->url;
        $exts = get_loaded_extensions();
        $mimeType = 'application/octet-stream';
        if(array_search('fileinfo', $exts)===FALSE)
        {
            $sizeInfo = getimagesize($file);
            $mimeType = $sizeInfo['mime'];
        }else{
            $mimeType = mime_content_type($file);
        }
        $Read = new Read();
        $Read->smartReadFile($file,$mimeType);
    }
}