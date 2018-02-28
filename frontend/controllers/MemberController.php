<?php
namespace frontend\controllers;
use backend\models\Book;
use backend\models\Question;
use frontend\models\Member;
use yii\db\Exception;
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

    //出题抽取现金红包
    public function actionLuckDraw(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            $id=\Yii::$app->request->post('id');
            //判断是否传入参数
            if(empty($phone) || empty($id)){
                $relust['msg']='请传入指定参数';
                return $relust;
            }
            //判断是否有该题
            $question=Question::findOne(['id'=>$id]);
            if(!$question){
                $relust['msg']='没有该题';
                return $relust;
            }

            //判断是否领取过红包
            if($question->receive==0){
                $relust['msg']='已经抽取过红包了';
                return $relust;
            }

            $member=Member::findOne(['phone'=>$phone]);
            $money=0;
            //判断是否该手机号
            if($member){
                $number=rand(1,10000);
                if($number<=9000){
                    $money=sprintf("%.2f",Member::getrandomFloat(0.06,0.1));
                }elseif ($number>9000 && $number<=9900){
                    $money=sprintf("%.2f",Member::getrandomFloat(0.1,0.5));
                }elseif ($number>9900 && $number<=10000){
                    $money=sprintf("%.2f",Member::getrandomFloat(0.5,1.2));
                }
                $member->money=$member->money+$money;
                $transaction=\Yii::$app->db->beginTransaction();//开启事务
                try{
                    $member->save();
                    $question->receive=0;
                    $question->save();
                    $transaction->commit();
                    $relust['code']=200;
                    $relust['msg']='出题抽取红包成功';
                    $relust['money']=$money;

                }catch (Exception $e ){
                    //事务回滚
                    $transaction->rollBack();
                    $relust['msg']='出题抽取红包失败';
                }
            }else{
                $relust['msg']='没有该手机号';
            }
        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }

    //答题抽取现金红包
    public function actionLotteryDraw(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            //判断是否传入参数
            if(empty($phone)){
                $relust['msg']='请传入指定参数';
                return $relust;
            }
            $member=Member::findOne(['phone'=>$phone]);
            $money=0;
            //判断是否该手机号
            if($member){
                $number=rand(1,10001);
                if($number<=9000){
                    $money=sprintf("%.2f",Member::getrandomFloat(0.06,0.1));
                }elseif ($number>9000 && $number<=9900){
                    $money=sprintf("%.2f",Member::getrandomFloat(0.1,0.5));
                }elseif ($number>9900 && $number<=10000){
                    $money=sprintf("%.2f",Member::getrandomFloat(0.5,1.2));
                }elseif ($number==10001){
                    $money=8.8;
                }
                $member->money=$member->money+$money;
                if($member->save()){
                    $relust['code']=200;
                    $relust['msg']='答题获取红包成功';
                    $relust['money']=$money;
                }else{
                    $relust['msg']='答题获取红包失败';
                }

            }else{
                $relust['msg']='没有该手机号';
            }
        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //答题错误抽取书券或者书
    public function actionExtract(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            //判断是否传入参数
            if(empty($phone)){
                $relust['msg']='未传入指定参数';
            }
            //判断数据库是否有该手机号
            $member=Member::findOne(['phone'=>$phone]);
            if(!$member){
                $relust['msg']='数据库无该手机号';
                return $relust;
            }
            //随机书和书券概率
            $num=rand(1,10);
            //随机数大于8送书,小于8送书券
            if($num>8){
                //抽取图书
                $book=\Yii::$app->db->createCommand('SELECT id,image FROM book WHERE `from`=3 ORDER BY RAND() LIMIT 1')->queryAll();
                if($member->book_id){
                    //分割数组
                    $BookId=explode(',',$member->book_id);
                    //判断是否已经抽到
                    if(!in_array($book[0]['id'],$BookId)){
                        $member->book_id=$member->book_id.','.$book[0]['id'];
                        $res=$member->save();
                    }else{
                        $relust['code']=401;
                        $relust['msg']='已存在该书';
                        return $relust;
                    }
                }else{
                    $member->book_id=$book[0]['id'];
                    $res=$member->save();
                }

                if($res){
                    $relust['code']=200;
                    $relust['msg']='送你一本书赶快去学习新知识吧';
                    $relust['img']=$book[0]['image'];
                }else{
                    $relust['msg']='抽取图书失败';
                }

            }else{
                //抽取书券
                $arr=[168,666];
                $voucher=$arr[array_rand($arr)];
                $member->voucher=$member->voucher+$voucher;
                if($member->save()){
                    if($voucher==168){
                        $relust['code']=200;
                        $relust['msg']='送你一张书券赶快去领取吧';
                        $relust['img']='http://www.nixiong.top/yuanxiao/img/168.jpg';
                    }else{
                        $relust['code']=200;
                        $relust['msg']='送你一张书券赶快去领取吧';
                        $relust['img']='http://www.nixiong.top/yuanxiao/img/666.jpg';
                    }
                }else{
                    $relust['msg']='抽取书券失败';
                }
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //查询用户答题次数
    public function actionQuestionNumber(){
        $relust=[
            'code'=>200,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收数据
            $phone=\Yii::$app->request->post('phone');
            $time=\Yii::$app->request->post('date');//当前时间
            if(empty($phone) || empty($time)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            //通过手机查找该用户
            $member=Member::findOne(['phone'=>$phone]);
            if(!$member){
                $relust['msg']='没有该手机号';
                return $relust;
            }

            if($time==20180301){
                $relust['msg']='获取一天答题次数成功';
                $relust['code']=200;
                $relust['frequency']=$member->one;
            }elseif ($time==20180302){
                $relust['msg']='获取第二天答题次数成功';
                $relust['code']=200;
                $relust['frequency']=$member->two;
            }elseif($time<20180301){
                $relust['msg']='活动未开始';
            }elseif ($time>20180302){
                $relust['msg']='活动已结束';
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //查询用户账户余额
    public function actionBalance(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            //判断是否传入参数
            if(empty($phone)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            $member=Member::findOne(['phone'=>$phone]);
            if(!$member){
                $relust['msg']='没有该手机号';
                return $relust;
            }
            $relust['code']=200;
            $relust['msg']='获取账户余额成功';
            $relust['money']=$member->money;

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
                $tmp[] = $arr[array_rand($arr)].'****'.mt_rand(1000,9999).'抽取到'.sprintf("%.2f",Member::getrandomFloat(0.1,8.8)).'元红包';
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