<?php
namespace frontend\controllers;
use backend\models\User;
use frontend\models\Sign;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

//用户签到控制器
class SignController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //签到添加
    public function actionAdd(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //验证接口
            //验证接口
            $obj=new Verification();
            $res=$obj->check();
            //if($res){
                //接口验证不通过
              //  $relust['msg']= $res;
           // }else{
                //接口验证通过
                //接收参数
                $user_id=\Yii::$app->request->post('user_id');
                //查询数据库是否有该用户
                $User=User::find()->where(['id'=>$user_id])->one();
                if($User){
                    $Sign=Sign::find()->where(['user_id'=>$user_id])->one();
                    $time=date("Ymd");
                    if($Sign){
                        //用户已经参加过签到
                        //判断是否是今日签到
                        if($Sign->last_sign_time==$time){
                            $relust['msg']='请勿重复签到';
                        }else{
                            $model2=Sign::find()->where(['user_id'=>$user_id])->one();
                            $model2->last_sign_time=$time;
                            $model2->check_num=$model2->check_num+1;
                            $model2->save();
                            $relust['code']=200;
                            $relust['msg']='签到成功';
                        }


                    }else{
                        //用户未参加过签到
                        $model=new Sign();
                        $model->user_id=$user_id;
                        $model->last_sign_time=$time;
                        $model->check_num=1;
                        $model->save();
                        $relust['code']=200;
                        $relust['msg']='签到成功';
                    }

                }else{
                    //数据库无该用户
                    $relust['msg']='无该用户';
                }
          //  }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }
}