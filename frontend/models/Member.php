<?php
namespace frontend\models;
//元宵节活动用户模型
use yii\db\ActiveRecord;

class Member extends ActiveRecord{

  public static function getrandomFloat($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public static function getTotalMember(){
      $TotolMember=Member::find()->count('id');
      if($TotolMember){
          return $TotolMember;
      }else{
          return 0;
      }

    }
}