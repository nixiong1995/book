<?php
namespace libs;
class Verification{
    public $token = 'yuekukuyue666888';
    //验证接口
    public function check(){
        if (\Yii::$app->request->isPost){
            $data = \Yii::$app->request->post();
        }else{
            $data = \Yii::$app->request->get();
        }
        //时间戳验证
        $time = isset($data['time'])?$data['time']:0;

        if($time){
            //请求有效期是1分钟
            if(time()-$time>20000 || ($time-60) >time()){
                $error ='请求已过期';
                return $error;
            }
        }else{
            $error='缺少参数';
            return $error;
        }
        //验证签名
        $sign = isset($data['sign'])?$data['sign']:'';
        if($sign){
            unset($data['sign']);
            ksort($data);
            $str = http_build_query($data);
            $s = strtoupper(md5($this->token.$str));
            if($sign == $s){
            }else{
                $error='签名错误';
                return $this->token.$str;
            }

        }else{
            $error='缺少参数';
            return $error;
        }
    }
}