<?php
//元宵节答题活动
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Question;
use yii\data\Pagination;
use yii\web\Controller;

class QuestionsController extends Controller{

    //题库列表
    public function actionIndex(){
        $keyword=\Yii::$app->request->get('keyword');
        $query=Question::find();
        if($keyword){
            $query->andWhere(['like','title',$keyword]);
        }else{
            $query->all();
        }

        $pager=new Pagination([
            'totalCount'=>$query->count(),//总条数
            'defaultPageSize'=>20,//每页显示条数
        ]);
        $models=$query->limit($pager->limit)->offset($pager->offset)->orderBy('create_time DESC')->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);
    }

    //题库审核
    public function actionEdit(){
        //接收修改题id
        $id=\Yii::$app->request->post('id');
        //接收修改题状态
        $status=\Yii::$app->request->post('status');
        $model=Question::findOne(['id'=>$id]);
        $model->status=$status;
        if($model->save()){
            //题目通过审核随机随机抽取现金或其他东西
            return 'success';
        }else{
            return 'error';
        }
    }

    //验证访问权限
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