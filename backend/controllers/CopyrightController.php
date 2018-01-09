<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Author;
use backend\models\Book;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\Controller;

class CopyrightController extends Controller{
    //版权方图书列表
    public function actionIndex(){
        $category_id=\Yii::$app->request->get('category');
        $book=\Yii::$app->request->get('book');//书名
        $author=\Yii::$app->request->get('author');//作者
        //查询数据库分类第一位
        if(!$book && !$author){
            $id=\Yii::$app->db->createCommand("SELECT id FROM category ")->queryScalar();
            $category_id=$category_id?$category_id:$id;
        }
        $where='';
        //$query=Book::find()->where(['status'=>1,'category_id'=>$category_id])->orderBy('groom_time DESC');
        if($author){
            $author_id=\Yii::$app->db->createCommand("SELECT id FROM author WHERE name='$author'")->queryScalar();
            //var_dump($author_id);exit;
            $where=" and author_id='$author_id'";
            //$query ->andWhere(['like','author_id',$author_id]);
        }
        if ($book){
            $where.=" and name like '%$book%'";
            //$query->andWhere(['like','name',$book]);
        }
        if ($category_id){
            $where.=" and category_id='$category_id'";
            //$query->andWhere(['category_id'=>$category_id]);
        }

        $total1=Book::find()->count('id');//数据库总书数量
        $total2=Book::find()->andWhere(['from'=>3])->count('id');//版权书书数量
        $count=Book::findBySql("SELECT * FROM book WHERE `from`=3 $where")->count();
        //实例化分页工具类
        $pager=new Pagination([
            'totalCount'=>$count,//总条数
            'defaultPageSize'=>20,//每页显示条数
        ]);
        $models=Book::findBySql("SELECT * FROM book WHERE `from`=3 $where ORDER by create_time DESC lIMIT $pager->offset,$pager->limit")->all();
        //分页查询
        // $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager,'total1'=>$total1,'total2'=>$total2]);

    }

    //版权方图书删除
    public function actionDel(){
        //接收图书id
        $id=\Yii::$app->request->post('id');
        //查找图书
        $book=Book::findOne(['id'=>$id]);
        //作者id
        $author_id=$book->author_id;
        //图书删除
        $res1=$book->delete();
        //查找该作者是否还有图书
        $relust=Book::findOne(['author_id'=>$author_id]);
        if(!$relust){
            $author=Author::findOne(['id'=>$author_id]);
            $res2=$author->delete();
        }
        if($res1 && $res2){
            return 'success';
        }else{
            return 'error';
        }



    }

    //版权方图书修改
    public function actionEdit($id){
        $model=Book::findOne(['id'=>$id]);
        $Author=Author::findOne(['id'=>$model->author_id]);
        $model->author_name=$Author->name;
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $Author->name=$model->author_name;
                $transaction=\Yii::$app->db->beginTransaction();//开启事务
                try{
                    //保存作者信息
                    $Author->save(false);
                    $model->author_id=$Author->id;
                    //保存所有数据
                    $model->save();
                    $transaction->commit();
                    \Yii::$app->session->setFlash('success', '版权书修改成功');

                }catch (Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                }

                return $this->redirect(['copyright/index']);
            }
        }
        return $this->render('edit',['model'=>$model]);

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