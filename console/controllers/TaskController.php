<?php
namespace console\controllers;
use backend\models\Recharge;
use yii\console\Controller;

class TaskController extends Controller{
    //手动清理超时未支付订单(24小时)
    public function actionClean(){
        //设置脚本执行时间(不终止)
        //set_time_limit(0);
        //当前时间 - 创建时间 > 24小时   ---> 创建时间 <  当前时间 - 24小时
        //超时未支付订单
        //sql: update order set status=0 where status = 1 and create_time < time()-24*3600
        //while (true){
            //Recharge::deleteAll('status=1 and create_time < '.(time()-60));
            Recharge::deleteAll('create_time < :create_time AND status = :status', [':create_time' =>(time()-300) , ':status' => '1']);
            //每隔一秒执行一次
            //sleep(1);
            echo '清理完成'.date('Y-m-d H:i:s')."\n";
       // }

    }
}