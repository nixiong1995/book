<?php
namespace frontend\controllers;
use backend\models\Book;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

//微信小程序
class SmallprogramController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //推荐图书列表
    public function actionGroom(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                $relust['msg']= $res;
            }else{
                //接收参数
                $category_id=\Yii::$app->request->post('category_id');
                if(empty($category_id)){
                    $relust='未传入指定参数';
                    return $relust;
                }
                $book=Book::find()->where(['category_id'=>$category_id])->andWhere(['<>','from',4])->orderBy('update_time DESC')->limit(12)->all();
                if($book){
                    $relust['code']=200;
                    $relust['data']=$book;
                    $relust['msg']='成功返回图书';
                }else{
                    $relust['msg']='没有数据';
                }
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

}