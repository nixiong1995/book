<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Settlement extends ActiveRecord{

    //验证规则
    public function rules()
    {
        return [
            [['payable','paid','poundage','remarks',],'required'],
            [['payable','paid','poundage'],'number'],
            ['remarks','string']
        ];
    }

    //字段中文命名
    public function attributeLabels()
    {
        return [
            'payable'=>'应付金额',
            'paid'=>'实付金额',
            'poundage'=>'手续费',
            'remarks'=>'备注',
        ];
    }

    //本月销售结算(每本书)
    public static function getMonthConsume($information_id){
        $totalMoney=\Yii::$app->db->createCommand("SELECT SUM(deduction) FROM consume WHERE book_id IN (SELECT id from book WHERE ascription=$information_id) AND DATE_SUB(CURDATE(), INTERVAL 1 MONTH)<=from_unixtime(create_time,'%Y-%m-%d')")->queryScalar();
        if($totalMoney){
            return sprintf("%.2f",$totalMoney/100);
        }else{
            return '0.00';
        }

    }

    //上月销售
    public static function getLastmonthConsume($information_id){
        $totalMoney=\Yii::$app->db->createCommand("SELECT SUM(deduction) FROM consume WHERE book_id IN (SELECT id from book WHERE ascription=$information_id) AND PERIOD_DIFF( date_format( now( ) , '%Y%m' ) , from_unixtime(create_time, '%Y%m' )) =1")->queryScalar();
        if($totalMoney){
            return sprintf("%.2f",$totalMoney/100);
        }else{
            return '0.00';
        }

    }

    //关联查询版权方名称
    public function getInformation(){
        return $this->hasOne(Information::className(),['id'=>'information_id']);
    }

    //查询本月是否已结算
    public static function getRelust($information_id){
       $relust= \Yii::$app->db->createCommand("SELECT id FROM settlement WHERE information_id=$information_id AND  DATE_SUB(CURDATE(), INTERVAL 1 MONTH)<=from_unixtime(create_time,'%Y-%m-%d')")->queryScalar();
       if($relust){
           return true;
       }else{
           return false;
       }
    }

}