<?php
namespace frontend\controllers;
use backend\models\Recharge;
use backend\models\User;
use frontend\alipay\aop\AopClient;
use frontend\alipay\aop\request\AlipayTradeAppPayRequest;
use libs\Verification;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;

//支付宝充值
class AlipayController extends Controller{

    public $enableCsrfValidation=false;
    private $confing=[
        'public_key'=>'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCpXTXIGlRgP/KuR2k3fxzH3GlzX/eTuMJoq+iSpMPYmEMQPEnE9tBMI2/daoahQ4ntObCdXPTJrUcFA1dGMlaGnLg9yZCJy87brMsPRbVYNeCsnX1XyJKs9747Qy8t+f4n6R+jO6eU9rFKtkyJveoHaAy+4GrIITzPRZNGLksZzQIDAQAB',
        'app_id' =>'2018010501599590',
        'private_key'=>'MIICXAIBAAKBgQCpXTXIGlRgP/KuR2k3fxzH3GlzX/eTuMJoq+iSpMPYmEMQPEnE9tBMI2/daoahQ4ntObCdXPTJrUcFA1dGMlaGnLg9yZCJy87brMsPRbVYNeCsnX1XyJKs9747Qy8t+f4n6R+jO6eU9rFKtkyJveoHaAy+4GrIITzPRZNGLksZzQIDAQABAoGATz+oTMvVM3x4yAfuOeOmyfZe+XesV1kazvOxzfx/D7ifmNc9BYcgDKCAVbDk8kyyG2jlNZ0rhbspAjV//v7K8Usx2P74XdiDtpKffNQUPJdyZHedhPRCo+JXs8FlJLMOiNSCJ/KsiQJrY2wxKtaeLdyErPuqotCTm3IYwP2V2JkCQQDVm+ZUVi1K1sBjQrAdCnv3iXR4Tw6JMZKZI48zUFwRzxvM+aN+IgBpYjm3wv9KPYp9NuuRG7+Gjw2S/FLtMOKjAkEAyvmDHHXownioJhHiegSpn7QiI8uOzhVip7PqaZZXfXggb97nTp9x7uTMLUO1oSl0ISBPfe1bn5V2t+FwUkJIzwJAY4wqBTe8F9qJAjk79ezC5RNr8f112r39gdyuic1zeuE4JYhZhxi1dGdQWrFHZAPWHJCRq6hw03arbsqkouFbXQJAF5APoGYvtyO6oXDCEdgouNl4fR9MXLAu27kPJWLGlVI0scf2ojHwUANPkJGjrCnbyVyu3beIQ2Zeeco599KqEQJBAMaACGkuziSZgsutzIVewJx5ajDWdFgOuVu0jl533GNKM+nJlQkwJUoScPOusV6oil47RA+ttOQOmzTK4YVm4q8=',

    ];

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }


    //生成订单信息
    public function actionOrder(){
        $relust=[
          'code'=>400,
          'msg'=>''  ,
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
           // if($res){
                //接口验证不通过
              //  $result['msg']= $res;
           // }else{
                //接收数据
                $order=[];
                $order['money']=\Yii::$app->request->post('order_money');//订单金额
                $order['content']=\Yii::$app->request->post('order_content');//订单内容
                $user_id=\Yii::$app->request->post('user_id');//用户id
                if(empty($order['money']) || empty($order['content']) || empty($user_id)){
                    $relust['msg']='请传入指定参数';
                    return $relust;

                }
                //生成唯一订单号
                $order['danhao'] = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                //查询数据库是否有该单号
                $r=Recharge::findOne(['no'=>$order['danhao']]);
                while ($r){
                    $order['danhao'] = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                    //查询数据库是否有该单号
                    $r=Recharge::findOne(['no'=>$order['danhao']]);
                }

                $voucher=0;//定义赠送书券
                //书券赠送
                if( $order['money']==30){
                    $voucher=500;
                }elseif ($order['money']==98){
                    $voucher=2000;
                }elseif ($order['money']==198){
                    $voucher=5000;
                }
                //将数据写入数据库
                $model=new Recharge();
                $model->user_id=$user_id;
                $model->no=$order['danhao'];
                $model->money=$order['money'];
                $model->ticket=$order['money']*100;
                $model->voucher=$voucher;

                $model->mode='支付宝';
                $model->status=1;
                $model->create_time=time();
                $model->save();
                return $this->AppCode($order);
                //$relust['code']=200;
                //$relust['msg']='获取参数成功';
                //$relust['data'][]=$rows;




           // }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }

    //支付宝生成手机端需要信息并返回
    public function AppCode($order)
    {

        $aop = new AopClient();
        //**沙箱测试支付宝开始
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        //实际上线app id需真实的
        $aop->appId = $this->confing['app_id']; //开发者appid
        $aop->rsaPrivateKey = $this->confing['private_key']; //填写工具生成的商户应用私钥
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA";
        $aop->alipayrsaPublicKey = $this->confing['public_key']; //填写从支付宝开放后台查看的支付宝公钥
        $bizcontent = json_encode([
            'body'=>$order['content'],
            'subject'=>'阅cool阅票',
            'out_trade_no'=>$order['danhao'],//此订单号为商户唯一订单号
            'total_amount'=>$order['money'],//保留两位小数
            //'total_amount'=>0.01,//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY'
        ]);
        //**沙箱测试支付宝结束
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
        //支付宝回调
        $request->setNotifyUrl("http://api.voogaa.cn/alipay/alipay-notify");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return  $response;


    }

    //支付宝异步通知
    public function actionAlipayNotify(){
        //验证签名
        $aop = new AopClient();
        $aop->alipayrsaPublicKey ='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA");
        //验签
        if($flag){
            //校验通知数据的正确性
            $out_trade_no = $_POST['out_trade_no'];//商户订单号
            $trade_no = $_POST['trade_no'];//支付宝交易号
            $trade_status = $_POST['trade_status'];//交易状态trade_status
            $total_amount = $_POST['total_amount'];//订单的实际金额
            $app_id = $_POST['app_id'];

            if($app_id!=$this->confing['app_id'])exit('fail');//验证app_id是否为该商户本身
            //只有交易通知状态为TRADE_SUCCESS或TRADE_FINISHED时，支付宝才会认定为买家付款成功。
            if($trade_status != 'TRADE_FINISHED' && $trade_status != 'TRADE_SUCCESS')exit('fail');
            //校验订单的正确性
            if(!empty($out_trade_no)){
                //1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号;
                $model=Recharge::findOne(['no'=>$out_trade_no]);
                if(!$model)exit('fail');
                //if($model->status==2)exit('fail');
                //2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
                if($model->money!=$total_amount){
                    $model->status=3;
                    $model->trade_no=$trade_no;
                    $model->over_time=time();
                    $model->save();
                    exit('fail');
                }
                //3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）。
                //上述1、2、3有任何一个验证不通过，则表明本次通知是异常通知，务必忽略。在上述验证通过后商户必须根据支付宝不同类型的业务通知，正确的进行不同的业务处理，并且过滤重复的通知结果数据。
                //校验成功后在response中返回success，校验失败返回failure
                $model->trade_no=$trade_no;
                $model->status=2;
                $model->over_time=time();
                $transaction=\Yii::$app->db->beginTransaction();//开启事务

                try{

                    $model->save();
                    //var_dump($model->user_id);exit;
                    ////////////用户账户开始///////////
                    $user=User::findOne(['id'=>$model->user_id]);
                    $user->ticket=$user->ticket+$model->ticket;//用户账户原本阅票+本次充值阅票
                    $user->voucher=$user->voucher+$model->voucher;//用户账户原本书券+本次赠送书券;
                    $user->save();
                    $transaction->commit();
                    echo 'success';
                    //////////用户账户结束//////////
                    }catch (Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                }


            }else{
                exit('fail');
            }

       }else{
            echo "fail";//验证签名失败

        }


    }

    //查询订单状态
    public function actionSelectOrder(){
        $relust=[
            'code'=>400,
            'msg'=>''  ,
        ];
        if(\Yii::$app->request->isPost){
                //验证接口
                $obj=new Verification();
                $res=$obj->check();
               // if($res){
                   // $result['msg']= $res;
               // }else{
                    $no=\Yii::$app->request->post('no');
                    if(!$no){
                        $relust['msg']='请传入订单号';
                        return $relust;
                    }
                    $model=Recharge::findOne(['no'=>$no]);

                    if($model){

                        $relust['data']=['user_id'=>$model->user_id,'no'=>$model->no,'money'=>$model->money,
                            'ticket'=>$model->ticket,'voucher'=>$model->voucher,'trade_no'=>$model->trade_no,
                            'mode'=>$model->mode,'status'=>$model->status,'create_time'=>$model->create_time,'over_time'=>$model->over_time];
                        $relust['code']=200;
                        $relust['msg']='获取订单信息成功';

                    }else{
                        $relust['msg']='没有该订单';
                    }
               // }

        }else{

            $relust['msg']='请求方式错误';
        }
        return $relust;

        }

}