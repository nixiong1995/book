<?php
namespace  frontend\models;
//怦然声动活动上传照片模型
use yii\db\ActiveRecord;

class Photos extends ActiveRecord{

    //查询已通过审核的照片数量
    public static function getTotalPhotos(){
        $total_photos=\Yii::$app->db->createCommand('SELECT count(id) FROM photos WHERE status=1')->queryScalar();
        return  $total_photos;
    }

}