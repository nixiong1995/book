<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Book;
use backend\models\Consume;
use backend\models\User;
use yii\base\Object;
use yii\data\Pagination;
use yii\web\Controller;

class ConsumeController extends Controller{

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

        $query=Consume::findBySql("SELECT * FROM consume WHERE 1=1 $where ");
        //实例化分页工具类
        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>10,//每页显示条数
        ]);
        //分页查询
        $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);
    }

    //书数据统计
    public function actionCount(){
        $begin_time=\Yii::$app->request->get('begin_time');//开始时间
        $end_time=\Yii::$app->request->get('end_time');//开始时间
        $where='';
        if($begin_time){
            $begin_time= $begin_time.'000000';//拼接时间戳,加上时分秒
            $begin_time=strtotime($begin_time);
            $where.=" and create_time>=$begin_time";
            //$query->andWhere(['>','created_at',$begin_time]);
        }
        if($end_time){
            $end_time=  $end_time.'235959';//拼接时间戳,加上时分秒
            $end_time=strtotime($end_time);
            $where.=" and create_time<=$end_time";
            //$query->andWhere(['<=','created_at',$end_time]);
        }
       //$count=Consume::find()->Where(['book_id' =>16])->count('deduction');
       $models=\Yii::$app->db->createCommand("select sell_tj.sellCount,sell_tj.sellMoney,sell_tj.book_id,book.name from (select count(*) as sellCount,sum(deduction) as sellMoney,book_id from consume WHERE 1=1 $where group by book_id ORDER BY sellCount DESC ) as sell_tj,book where book.id = sell_tj.book_id  ")->queryAll();
        //$query=Consume::findBySql("select sell_tj.sellCount,sell_tj.sellMoney,sell_tj.book_id,book.name from (select count(*) as sellCount,sum(deduction) as sellMoney,book_id from consume group by book_id) as sell_tj,book where book.id = sell_tj.book_id")->all();
        //var_dump($models);exit;
        //$ids=\Yii::$app->db->createCommand("SELECT id FROM book")->queryColumn();
       // foreach ($ids as $id){
          //  $query=Consume::find()->where(['book_id'=>$id]);
        //}
        //$query=Consume::find();
       /* $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>10,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->queryScalar();*/
        //var_dump($models);exit;
        return $this->render('count',['models'=>$models]);

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