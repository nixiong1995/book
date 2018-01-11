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

    public function actionApppay(){
        $config_value=[
            'app_id' => '2018010501599590',
           'private_key'=>'MIICXAIBAAKBgQCpXTXIGlRgP/KuR2k3fxzH3GlzX/eTuMJoq+iSpMPYmEMQPEnE9tBMI2/daoahQ4ntObCdXPTJrUcFA1dGMlaGnLg9yZCJy87brMsPRbVYNeCsnX1XyJKs9747Qy8t+f4n6R+jO6eU9rFKtkyJveoHaAy+4GrIITzPRZNGLksZzQIDAQABAoGATz+oTMvVM3x4yAfuOeOmyfZe+XesV1kazvOxzfx/D7ifmNc9BYcgDKCAVbDk8kyyG2jlNZ0rhbspAjV//v7K8Usx2P74XdiDtpKffNQUPJdyZHedhPRCo+JXs8FlJLMOiNSCJ/KsiQJrY2wxKtaeLdyErPuqotCTm3IYwP2V2JkCQQDVm+ZUVi1K1sBjQrAdCnv3iXR4Tw6JMZKZI48zUFwRzxvM+aN+IgBpYjm3wv9KPYp9NuuRG7+Gjw2S/FLtMOKjAkEAyvmDHHXownioJhHiegSpn7QiI8uOzhVip7PqaZZXfXggb97nTp9x7uTMLUO1oSl0ISBPfe1bn5V2t+FwUkJIzwJAY4wqBTe8F9qJAjk79ezC5RNr8f112r39gdyuic1zeuE4JYhZhxi1dGdQWrFHZAPWHJCRq6hw03arbsqkouFbXQJAF5APoGYvtyO6oXDCEdgouNl4fR9MXLAu27kPJWLGlVI0scf2ojHwUANPkJGjrCnbyVyu3beIQ2Zeeco599KqEQJBAMaACGkuziSZgsutzIVewJx5ajDWdFgOuVu0jl533GNKM+nJlQkwJUoScPOusV6oil47RA+ttOQOmzTK4YVm4q8=',
            'public_key'=>'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCpXTXIGlRgP/KuR2k3fxzH3GlzX/eTuMJoq+iSpMPYmEMQPEnE9tBMI2/daoahQ4ntObCdXPTJrUcFA1dGMlaGnLg9yZCJy87brMsPRbVYNeCsnX1XyJKs9747Qy8t+f4n6R+jO6eU9rFKtkyJveoHaAy+4GrIITzPRZNGLksZzQIDAQAB',
        ];
        $order=['order_sn'=>'00001','order_amount'=>100];
        var_dump($this->actionGetAppcode($order,$config_value));


    }
}