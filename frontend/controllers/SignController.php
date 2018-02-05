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
        var_dump(time());exit;
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
}