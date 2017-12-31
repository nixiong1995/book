<?php
namespace frontend\controllers;
use backend\models\Book;
use backend\models\Reading;
use backend\models\User;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

//用户已读书记录
class ReadingController extends Controller{
    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionAdd(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
           // if($res){
               // $result['msg']= $res;
           // }else{
                //接收手机端传递过来的数据
                $book_id=\Yii::$app->request->post('book_id');
                $user_id=\Yii::$app->request->post('user_id');
                if($book_id && $user_id){
                    //判断数据库是否有该用户和该书
                    $book=Book::findOne(['id'=>$book_id]);
                    if(!$book){
                        $result['msg']='没有该书';
                        return $result;
                    }
                    $user=User::findOne(['id'=>$user_id]);
                    if(!$user){
                        $result['msg']='没有该用户';
                        return $result;
                    }
                    //查询用户是否已经读过该书
                    $res=Reading::findOne(['user_id'=>$user_id,'book_id'=>$book_id]);
                    if($res){
                        $result['msg']='数据库已存在该书';
                    }else{
                        $book=Book::findOne(['id'=>$book_id]);
                        $book->clicks=$book->clicks+1;//该书观看数加1
                        $book->save();
                        $model=new Reading();
                        $model->user_id=$user_id;
                        $model->book_id=$book_id;
                        $model->create_time=time();
                        if($model->save()){
                            $result['code']=200;
                            $result['msg']='加入用户已读书籍成功';
                        }else{
                            $result['msg']='加入用户已读书籍失败';
                        }
                    }
                }else{
                    $result['msg']='缺少参数';
                }
            //}
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}