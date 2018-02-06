<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Consume extends ActiveRecord{

    //关联查询用户表
    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

    //关联查询书表
    public function getBook(){
        return $this->hasOne(Book::className(),['id'=>'book_id']);
    }

    //查询累计消费阅票
    public static function getTotal(){
        $total_ticket=\Yii::$app->db->createCommand('SELECT SUM(deduction) FROM consume')->queryScalar();
        return $total_ticket;
    }

    //查询本月消费阅票合计
    public static function  getMonth(){
        $month_ticket=\Yii::$app->db->createCommand('SELECT SUM(deduction) FROM consume WHERE DATE_SUB(CURDATE(), INTERVAL 1 MONTH)<=from_unixtime(create_time,\'%Y-%m-%d\')')->queryScalar();
        return $month_ticket;
    }

    //查询近7天消费阅票累计
    public static function getWeek(){
        $week_ticket=\Yii::$app->db->createCommand('SELECT SUM(deduction) FROM consume WHERE DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= from_unixtime(create_time,\'%Y-%m-%d\')')->queryScalar();
        return $week_ticket;
    }

    //查询当日消费阅票累计
    public static function getToday(){
        $today_ticket=\Yii::$app->db->createCommand('select SUM(deduction) FROM consume where to_days (from_unixtime(create_time,\'%Y-%m-%d\'))= to_days(now())')->queryScalar();
        return $today_ticket;
    }

}