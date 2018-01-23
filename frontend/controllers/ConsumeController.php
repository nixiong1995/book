<?php
namespace frontend\controllers;
use backend\models\Book;
use DeepCopy\f001\B;
use libs\Verification;
use yii\web\Controller;
//消费接口

class  ConsumeController extends Controller{

    public function actionIndex(){
        $relust=[
          'code'=>400,
          'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //验证
            $obj = new Verification();
            $res = $obj->check();
            if($res){
             $result['msg']= $res;
            }else{
                //接收手机端传递的参数
                $book_id=\Yii::$app->request->post('book_id');//图书id
                $chapter_id=\Yii::$app->request->post('chapter_id');//章节id
                $user_id=\Yii::$app->request->post('user_id');//用户id

                //查询该书价格以及出处
                $book=Book::findOne(['id'=>$book_id]);









            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }
}