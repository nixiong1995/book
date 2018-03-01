<?php
//元宵节活动题库模型
namespace backend\models;
use yii\db\ActiveRecord;

class Question extends ActiveRecord{

    //判断是否是索引数组方法
    public static function is_assoc($array) {
        if(is_array($array)) {
            $keys = array_keys($array);
            return $keys !== array_keys($keys);
        }
        return false;
    }

    //统计用户抽取红包金额
    public static function getTotalMoney(){
        $total_money=\Yii::$app->db->createCommand('SELECT sum(money) FROM member')->queryScalar();
        return $total_money;
    }


}