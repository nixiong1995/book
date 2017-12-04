<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Recharge;
use backend\models\User;
use yii\data\Pagination;
use yii\web\Controller;

class RechargeController extends Controller{

    public function actionIndex(){
        $tel=\Yii::$app->request->get('tel');//手机
        $begin_time=\Yii::$app->request->get('begin_time');//搜索起始时间
        $end_time=\Yii::$app->request->get('end_time');//搜索结束时间
        $where='';
        if($tel){
            $id=\Yii::$app->db->createCommand("SELECT id FROM user WHERE tel='$tel'")->queryScalar();
            $where=" and user_id like '%$id%'";
        }
        if($begin_time){
            $begin_time= $begin_time.'000000';//拼接时间戳,加上时分秒
            $begin_time=strtotime($begin_time);
            $where.=" and create_time>=$begin_time";

        }
        if($end_time){
            $end_time=$end_time.'235959';//拼接时间戳,加上时分秒
            $end_time=strtotime($end_time);
            $where.=" and create_time<=$end_time";
        }

        $query=Recharge::findBySql("SELECT * From recharge WHERE money>0 $where ");
        //实例化分页工具类
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>10,//每页显示条数
        ]);
        //分页查询
        $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);
    }

    public function behaviors()
    {
        return [
            'rbac'=>[
                'class'=>RbacFilter::className(),
                'except'=>['login','logout','captcha','error'],
            ]
        ];
    }

}