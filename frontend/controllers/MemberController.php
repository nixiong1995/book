<?php
namespace frontend\controllers;
use frontend\models\Member;
use yii\web\Controller;
use yii\web\Response;
header("Access-Control-Allow-Origin: *");
//元宵节活动用户控制器
class MemberController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //记录用户
    public function actionRecord(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $phone=\Yii::$app->request->post('phone');
            //判断是否传入手机号
            if(empty($phone)){
                $result['msg']='请传入指定参数';
                return $result;
            }

            //判断数据库是否有该用户
            $member=Member::findOne(['phone'=>$phone]);
            if( $member){
                $result['msg']='数据库已有该用户';
                return $result;
            }

            //记录用户数据
            $model=new Member();
            $model->phone=$phone;
            $model->create_time=time();
            if($model->save()){
                $result['msg']='记录用户成功';
                $result['code']=200;
            }else{
                $result['msg']='记录用户失败';
            }


        }else{
            $result['msg']='请求方式错误';
        }
        return $result;

    }

    //随机抽取红包
    public function actionLuckDraw(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            $member=Member::findOne(['phone'=>$phone]);
            $money=0;
            if($member){
                $number=rand(1,10001);
                if($number<=8000){
                    $money=sprintf("%.2f",Member::getrandomFloat(0.06,0.1));
                }elseif ($number>8000 && $number<=9500){
                    $money=sprintf("%.2f",Member::getrandomFloat(0.1,0.5));
                }elseif ($number>9500 && $number<=10000){
                    $money=sprintf("%.2f",Member::getrandomFloat(0.5,1.2));
                }elseif ($number==10001){
                    $money=8.8;
                }
                $member->money=$member->money+$money;
                if($member->save()){
                    $relust['code']=200;
                    $relust['msg']='抽取红包'.$money.'元';
                }else{
                    $relust['msg']='抽取红包失败';
                }

            }else{
                $relust['msg']='没有该手机号';

            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }


    //轮播用户中奖信息
    public function actionWinning(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            $arr = array(
                130,131,132,133,134,135,136,137,138,139,
                144,147,
                150,151,152,153,155,156,157,158,159,
                176,177,178,
                180,181,182,183,184,185,186,187,188,189,
            );
            for($i = 0; $i < 10; $i++) {
                $tmp[] = $arr[array_rand($arr)].'****'.mt_rand(1000,9999).'抽取到'.sprintf("%.2f",Member::getrandomFloat(0.1,1.2)).'元红包';
            }
            $relust['code']=200;
            $relust['msg']='获取抽奖信息成功';
            $relust['data']=array_unique($tmp);
        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }
}