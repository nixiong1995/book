<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Recharge extends ActiveRecord{

    //关联查询用户表
    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

    //累计充值金额统计
    public static function getToatalMoney(){
        $total_money=\Yii::$app->db->createCommand("SELECT SUM(money) FROM recharge")->queryScalar();
        return $total_money;
    }

    //查询本月消费阅票合计
    public static function  getMonthMoney(){
        $month_money=\Yii::$app->db->createCommand('SELECT SUM(money) FROM recharge WHERE DATE_SUB(CURDATE(), INTERVAL 1 MONTH)<=from_unixtime(create_time,\'%Y-%m-%d\')')->queryScalar();
        return $month_money;
    }

    //查询近7天消费阅票累计
    public static function getWeekMoney(){
        $week_money=\Yii::$app->db->createCommand('SELECT SUM(money) FROM recharge WHERE DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= from_unixtime(create_time,\'%Y-%m-%d\')')->queryScalar();
        return $week_money;
    }

    //查询当日消费阅票累计
    public static function getTodayMoney(){
        $today_money=\Yii::$app->db->createCommand('select SUM(money) FROM recharge where to_days (from_unixtime(create_time,\'%Y-%m-%d\'))= to_days(now())')->queryScalar();
        return $today_money;
    }
}