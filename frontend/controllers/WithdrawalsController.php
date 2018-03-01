<?php
//元宵节活动提现记录
namespace frontend\controllers;
use backend\models\User;
use frontend\models\Member;
use frontend\models\Withdrawals;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;

class WithdrawalsController extends Controller{
    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionIndex(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            $alipay=\Yii::$app->request->post('alipay');
            if(empty($phone) || empty($alipay)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            //判断用户是否参与该活动
            $member=Member::findOne(['phone'=>$phone]);
            //账户余额
            $money=$member->money;
            if(!$member){
                $relust['msg']='您未参与该活动';
                return $relust;
            }

            //判断余额是否大于1元
            if($money<1){
                $relust['msg']='您的余额不足1元,无法提现';
                return $relust;
            }

            //判断是否是阅cool用户
            $user=User::findOne(['tel'=>$phone]);
            if(!$user){
                $relust['msg']='注册阅cool用户即可提现';
                return $relust;
            }

            $member->money=0;
            $transaction=\Yii::$app->db->beginTransaction();//开启事务
            try{
                $member->save();
                $model=new Withdrawals();
                $model->money=$money;
                $model->alipay=$alipay;
                $model->phone=$phone;
                $model->create_time=time();
                $model->save();
                $relust['code']=200;
                $relust['msg']='提现申请提交成功';
                $transaction->commit();
            }catch (Exception $e){
                //事务回滚
                $relust['msg']='提现申请提交失败';
                $transaction->rollBack();
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }
}