<?php
namespace frontend\controllers;
use frontend\alipay\aop\AopClient;
use frontend\alipay\aop\request\AlipayTradeAppPayRequest;
use frontend\models\SmsDemo;
use yii\web\Controller;

class SignController extends Controller
{
    public $token = 'yuekukuyue666888';

    public function actionSign()
    {
        $sql=\Yii::$app->db->createCommand('SELECT id FROM book WHERE id >= ((SELECT MAX(id) FROM book)-(SELECT MIN(id) FROM book)) * RAND() + (SELECT MIN(id) FROM book)  LIMIT 7')->queryColumn();
        var_dump($sql);exit;
        $time=strtotime("2017-12-22");
        var_dump($time);exit;
       // $p = ['keyword' =>'大佬', 'time' => 1513220354];
        $p = ['imei' =>'26B185DD-38A1-4BC3-8229-D31FE5E01F4', 'address' =>'四川省成都市','time'=>1513607650];
        //$p=['time'=>1513004884,'category_id'=>12,'page'=>1,'type'=>1];
        //1.对key做升序排列 //['a'=>'','b'=>'','c'=>'','time'=>'']
        ksort($p);
        //2. 将参数拼接成字符串 a=4&b=123&c=77&time=12312312
        $s = urldecode(http_build_query($p));
        //3 将token拼接到字符串前面.然后做md5运算,将结果转换成大写
        $sign = strtoupper(md5($this->token . $s));
        var_dump($sign);
    }

    public function actionSms($phone)
    {
        $demo = new SmsDemo(
            "LTAIypgT6xAIPdMq",
            "tneztyzfbgbMVRB87TFKrBUhMv3HnM"
        );

        $captcha = rand(100000, 999999);
        echo "SmsDemo::sendSms\n";
        $response = $demo->sendSms(
            "阅cool书城", // 短信签名
            "SMS_117515881", // 短信模板编号
            "$phone", // 短信接收者
            Array(  // 短信模板中字段的值
                "code" => $captcha,
            ),
            "123"
        );
        print_r($response);

        echo "SmsDemo::queryDetails\n";
        $response = $demo->queryDetails(
            "12345678901",  // phoneNumbers 电话号码
            "20170718", // sendDate 发送时间
            10, // pageSize 分页大小
            1 // currentPage 当前页码
        // "abcd" // bizId 短信发送流水号，选填
        );

        print_r($response);
    }

    public function actionGetAppcode($order, $config_value)
    {
        /*require_once('aop/AopClient.php');
        require_once('aop/request/AlipayTradeAppPayRequest.php');*/
        $aop = new AopClient();
        //**沙箱测试支付宝开始
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        //实际上线app id需真实的
        $aop->appId = $config_value['app_id']; //开发者appid
        $aop->rsaPrivateKey = $config_value['private_key']; //填写工具生成的商户应用私钥
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA";
        $aop->alipayrsaPublicKey = $config_value['public_key']; //填写从支付宝开放后台查看的支付宝公钥
        $bizcontent = json_encode([
            'body'=>$order['order_sn'],
            'subject'=>'***',
            'out_trade_no'=>$order['order_sn'],//此订单号为商户唯一订单号
            'total_amount'=> $order['order_amount'],//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY'
        ]);
        //**沙箱测试支付宝结束
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
        //支付宝回调
        $request->setNotifyUrl('http://admin.voogaa.cn',true,true);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return $response;
    }

    public function actionAlipayNotify(){
        //验证签名
        $aop = new AopClient();
        $aop->alipayrsaPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCpXTXIGlRgP/KuR2k3fxzH3GlzX/eTuMJoq+iSpMPYmEMQPEnE9tBMI2/daoahQ4ntObCdXPTJrUcFA1dGMlaGnLg9yZCJy87brMsPRbVYNeCsnX1XyJKs9747Qy8t+f4n6R+jO6eU9rFKtkyJveoHaAy+4GrIITzPRZNGLksZzQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        //验签
        if($flag){
            //校验通知数据的正确性
            $out_trade_no = $_POST['out_trade_no'];//商户订单号
            $trade_no = $_POST['trade_no'];//支付宝交易号
            $trade_status = $_POST['trade_status'];//交易状态trade_status
            $total_amount = $_POST['total_amount'];//订单的实际金额
            $app_id = $_POST['app_id'];
            if($app_id!=$this->config['app_id'])exit('fail');//验证app_id是否为该商户本身
            //只有交易通知状态为TRADE_SUCCESS或TRADE_FINISHED时，支付宝才会认定为买家付款成功。
            if($trade_status != 'TRADE_FINISHED' && $trade_status != 'TRADE_SUCCESS')exit('fail');
            //校验订单的正确性
            if(!empty($out_trade_no)){
                //1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号;
               //2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
              //3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）。
            //上述1、2、3有任何一个验证不通过，则表明本次通知是异常通知，务必忽略。在上述验证通过后商户必须根据支付宝不同类型的业务通知，正确的进行不同的业务处理，并且过滤重复的通知结果数据。
            //校验成功后在response中返回success，校验失败返回failure
            }
            exit('fail');
        }

        echo"fail"; //验证签名失败

    }


}