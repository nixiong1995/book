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
            if($path==false){
                $relust['msg']='音频转换失败';
            }else{
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
            }


        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }

}