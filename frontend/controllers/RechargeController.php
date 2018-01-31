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
                 //接收客户端参数
                 //校验通知数据的正确性
                /* $out_trade_no = $_POST['out_trade_no'];//商户订单号
                 $trade_no = $_POST['trade_no'];//支付宝交易号
                 $trade_status = $_POST['trade_status'];//交易状态trade_status
                 $total_amount = $_POST['total_amount'];//订单的实际金额
                 $app_id = $_POST['app_id'];*/
                $out_trade_no=\Yii::$app->request->post('out_trade_no');//商户订单号
                $trade_no=\Yii::$app->request->post('trade_no');//第三方交易号
                $total_amount=\Yii::$app->request->post('total_amount');//订单的实际金额
                 //根据本地订单号查询到该订单
                 $recharge=Recharge::findOne(['no'=>$out_trade_no]);
                 //判断是否有该订单
                 if(!$recharge){
                     $relust['msg']='没有该订单';
                     return $relust;
                 }

                 //判断价格是否一样
                 if($recharge->money!=$total_amount){
                     $relust['msg']='价格有误';
                     return $relust;
                 }

                 //修改订单
                $recharge->trade_no=$trade_no;
                $recharge->status=2;
                $recharge->over_time=time();
                $transaction=\Yii::$app->db->beginTransaction();//开启事务

                try{

                    $recharge->save();
                    //var_dump($model->user_id);exit;
                    ////////////用户账户开始///////////
                    $user=User::findOne(['id'=>$recharge->user_id]);
                    $user->ticket=$user->ticket+$recharge->ticket;//用户账户原本阅票+本次充值阅票
                    $user->voucher=$user->voucher+$recharge->voucher;//用户账户原本书券+本次赠送书券;
                    $user->save();
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