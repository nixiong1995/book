<?php
namespace frontend\controllers;
use frontend\models\Audio;
use yii\web\Controller;
use yii\web\Response;

class ActivityController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //上传音频文件
    public function actionUploadAudio(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            $audio_id=\Yii::$app->request->post('audio_id');
            $audio_content_id=\Yii::$app->request->post('audio_content_id');
            $member_id=\Yii::$app->request->post('member_id');
            $duration=\Yii::$app->request->post('duration');
            if(empty($audio_id) || empty($member_id) || empty($duration) || empty($audio_content_id)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            $path=Audio::getUpload( $audio_id);
            $model=new Audio();
            $model->audio_content_id=$audio_content_id;
            $model->member_id=$member_id;
            $model->path= $path;
            $model->duration=$duration;
            $model->create_time=time();
            if($model->save()){
                $relust['code']=200;
                $relust['msg']='存入音频成功';
            }else{
                $relust['msg']='存入音频失败';
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }

    function upload(){
        $media_id = $_POST["media_id"];
        $access_token = getAccessToken();

        $path = "./weixinrecord/";   //保存路径，相对当前文件的路径
        $outPath = "./php/weixinrecord/";  //输出路径，给show.php 文件用，上一级

        if(!is_dir($path)){
            mkdir($path);
        }

        //微 信上传下载媒体文件
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$media_id}";

        $filename = "wxupload_".time().rand(1111,9999).".amr";
        downAndSaveFile($url,$path."/".$filename);

        $data["path"] = $outPath.$filename;
        $data["msg"] = "download record audio success!";
        // $data["url"] = $url;

        echo json_encode($data);
    }


    //根据URL地址，下载文件
    function downAndSaveFile($url,$savePath){
        ob_start();
        readfile($url);
        $img  = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        $fp = fopen($savePath, 'a');
        fwrite($fp, $img);
        fclose($fp);
    }
}