<?php
//怦然声动控制器
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Question;
use frontend\models\Audio;
use frontend\models\Photos;
use yii\data\Pagination;
use yii\web\Controller;

class ActivityController extends Controller{

    //照片列表
    public function actionPhoto(){
        $query=Photos::find();
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>20,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->orderBy('create_time DESC')->all();
        return $this->render('photo',['models'=>$models,'pager'=>$pager]);
    }

    //音频列表
    public function actionAudio(){
        $query=Audio::find();
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>20,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->orderBy('praise DESC')->all();
        return $this->render('audio',['models'=>$models,'pager'=>$pager]);
    }

    //照片审核
    public function actionPhotoCheck(){
        //接收修改题id
        $id=\Yii::$app->request->post('id');
        //接收修改题状态
        $status=\Yii::$app->request->post('status');
        $model=Photos::findOne(['id'=>$id]);
        $model->status=$status;
        if($model->save()){
            //题目通过审核随机随机抽取现金或其他东西
            return 'success';
        }else{
            return 'error';
        }
    }

    //音频审核
    public function actionAudioCheck(){
        //接收修改题id
        $id=\Yii::$app->request->post('id');
        //接收修改题状态
        $status=\Yii::$app->request->post('status');
        $model=Audio::findOne(['id'=>$id]);
        $model->status=$status;
        if($model->save()){
            //题目通过审核随机随机抽取现金或其他东西
            return 'success';
        }else{
            return 'error';
        }
    }

    //修改点赞数
    public function actionModifiedValue(){
        //接收修改音频id
        $id=\Yii::$app->request->post('id');
        //接收修改题状态
        $praise=\Yii::$app->request->post('praise');
        $model=Audio::findOne(['id'=>$id]);
        $model->praise=$praise;
        if($model->save()){
            //题目通过审核随机随机抽取现金或其他东西
            return 'success';
        }else{
            return 'error';
        }
    }

    //验证访问权限
    public function behaviors()
    {
        return [
            'rbac'=>[
                'class'=>RbacFilter::className(),
                'except'=>['login','logout','captcha','error'],
            ]
        ];
    }
}