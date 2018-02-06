<?php
namespace frontend\controllers;
use backend\models\Recharge;
use common\models\User;
use libs\Verification;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;

//充值记录
class RechargeController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionRecord(){
        $relust=[
            'code'=>400,
            'msg'=>''  ,
        ];
        if(\Yii::$app->request->isPost){
            //验证接口
            $obj=new Verification();
            $res=$obj->check();
             //if($res){
            // $result['msg']= $res;
            // }else{
                $trade_no=\Yii::$app->request->post('trade_no');//第三方交易号
                $total_amount=\Yii::$app->request->post('total_amount');//订单的实际金额
                $user_id=\Yii::$app->request->post('user_id');//用户id
                if(empty($total_amount) ||empty($trade_no) ||empty($user_id)){
                    $relust['msg']='未传入指定参数';
                    return $relust;
                }

                //判断是否有该用户
                $user=User::findOne(['id'=>$user_id]);
                if(!$user){
                    $relust['msg']='没有该用户';
                    return $relust;
                }

                 $recharge=new Recharge();
                //生成唯一订单号
                $no = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                //查询数据库是否有该单号
                $r=Recharge::findOne(['no'=>$no]);
                while ($r){
                    $order['danhao'] = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                    //查询数据库是否有该单号
                    $r=Recharge::findOne(['no'=>$no]);
                }
                $voucher=0;//定义赠送书券
                //书券赠送
                if( $total_amount==30){
                    $voucher=500;
                }elseif ($total_amount==98){
                    $voucher=2000;
                }elseif ($total_amount==198){
                    $voucher=5000;
                }
                $recharge->no=$no;
                $recharge->user_id=$user_id;
                $recharge->money=$total_amount;
                $recharge->ticket=$total_amount*100;
                $recharge->voucher=$voucher;
                $recharge->trade_no=$trade_no;
                $recharge->mode='苹果支付';
                $recharge->status=2;
                $recharge->create_time=time();
                $recharge->over_time=time();

                $transaction=\Yii::$app->db->beginTransaction();//开启事务

                try{
                    $recharge->save();
                    ////////////用户账户开始///////////
                    $user->ticket=$user->ticket+$recharge->ticket;//用户账户原本阅票+本次充值阅票
                    $user->voucher=$user->voucher+$recharge->voucher;//用户账户原本书券+本次赠送书券;
                    $user->save(false);
                    $relust['code']=200;
                    $relust['msg']='记录用户充值成功';
                    $transaction->commit();

                    //////////用户账户结束//////////
                }catch (Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                }

          //   }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }
}