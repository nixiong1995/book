<?php
namespace frontend\controllers;
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

    public function actionDownload()
    {
        $result = [
            'code' => 400,
            'msg' => '',//错误信息,如果有
        ];
        if (\Yii::$app->request->isGet) {
             $obj=new Verification();
            $res=$obj->check();
           if($res){
             $result['msg']= $res;
            }else{
            $book_id = \Yii::$app->request->get('book_id');
            $no = \Yii::$app->request->get('no');
            if ($book_id != null && $no != null) {
                $file = \Yii::$app->db->createCommand("SELECT path FROM chapter WHERE book_id=$book_id AND no=$no")->queryScalar();
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
                $result['code']=200;
                $result['msg']='下载成功';


            }else{
                $result['msg']='缺少下载参数';
            }
            }
            }else {
                $result['msg'] = '请求方式错误';
            }
            return $result;
        }

}