<?php
namespace frontend\controllers;
use yii\web\Controller;

class SignController extends Controller{
    public $token = 'yuekukuyue666888';
    public function actionSign(){
        //var_dump(time());exit;
        //$p = ['tel'=>13880646145,'password'=>123456,'time'=>1512554300,'captcha'=>162496];
        $p=['captcha'=>123,'time'=>123,'tel'=>123,'password'=>123];
        //1.对key做升序排列 //['a'=>'','b'=>'','c'=>'','time'=>'']
        ksort($p);

        //2. 将参数拼接成字符串 a=4&b=123&c=77&time=12312312
        $s = http_build_query($p);
        var_dump($s);exit;
        //3 将token拼接到字符串前面.然后做md5运算,将结果转换成大写
        $sign = strtoupper(md5($this->token.$s));
        var_dump($sign);
    }
}