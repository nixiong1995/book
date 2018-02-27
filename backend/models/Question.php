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

    //查询已通过审核的题目数量
    public static function getTotalQuestion(){
        $total_question=\Yii::$app->db->createCommand('SELECT count(id) FROM question WHERE status=4')->queryScalar();
        return $total_question;
    }


}