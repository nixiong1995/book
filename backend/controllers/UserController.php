<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Author;
use backend\models\Book;
use backend\models\Category;
use backend\models\Purchased;
use backend\models\Reading;
use backend\models\User;
use backend\models\UserDetails;
use yii\data\Pagination;
use yii\web\Controller;

class UserController extends Controller{
    //用户列表
    public function actionIndex(){
        //接收搜索关键字
        $keyword=\Yii::$app->request->get('keyword');//邮箱,手机,账号
        $begin_time=\Yii::$app->request->get('begin_time');//搜索起始时间
        $end_time=\Yii::$app->request->get('end_time');//搜索结束时间
        $address=\Yii::$app->request->get('address');//地域
        $source=\Yii::$app->request->get('source');//地域
        $where='';
       if($keyword){
           $where=" and tel like '%$keyword%' or uid like '%$keyword%'";
          /*  $query ->Where([//搜索条件
                'or',
                ['like','tel',$keyword],
                ['like','uid',$keyword],
                ['like','email',$keyword],
            ]);*/
        }
        if($begin_time){
           $begin_time= $begin_time.'000000';//拼接时间戳,加上时分秒
           $begin_time=strtotime($begin_time);
            $where.=" and created_at>=$begin_time";
            //$query->andWhere(['>','created_at',$begin_time]);
        }
        if($end_time){
            $end_time=  $end_time.'235959';//拼接时间戳,加上时分秒
            $end_time=strtotime($end_time);
            $where.=" and created_at<=$end_time";
            //$query->andWhere(['<=','created_at',$end_time]);
        }
        if ($address){
            $where.=" and address like '%$address%'";
            //$query->andWhere(['like','address',$address]);
       }
       if ($source){
           $where.=" and source like '%$source%'";
           //$query->andWhere(['like','source',$source]);
       }
        $count=User::findBySql("SELECT * FROM user WHERE 1=1 $where ")->count();
        //实例化分页工具类
        $pager=new Pagination([
            'totalCount'=>$count,//总条数
            'defaultPageSize'=>30,//每页显示条数
        ]);
        //分页查询
        $models=User::findBySql("SELECT * FROM user WHERE 1=1 $where limit $pager->offset,$pager->limit")->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);
    }

    public function actionDetail($id)
    {
        $model = UserDetails::findOne(['user_id' => $id]);
        if($model->f_type){
            $category_ids=explode('|',$model->f_type);//分割喜欢的类型字段为数组
            $category_ids=array_filter($category_ids);//删除数组中空元素
            //通过分类id遍历查询喜欢的类型
            $molds=[];//定义空数组装分类名
            foreach ($category_ids as $category_id){
                $category=Category::findBySql("SELECT id,name FROM category where id=$category_id")->one();
                $molds[$category->id]=$category->name;//将分类名装入数组中
            }
            $TypeName=implode('|',$molds);//分割数组成字符串
        }else{
            $TypeName=null;
        }

        //遍历查询书名
        if($model->collect){
            $collects=explode('|',$model->collect);//分割收藏的书为数组
            $collects=array_filter($collects);//删除数组中空元素
            $books2=[];
            foreach ($collects as $collect) {
                $Books= Book::findBySql("SELECT id,name FROM book where id=$collect")->one();
                $books2[$Books->id]= $Books->name;//将书名装入数组中
            }
            $BookName3=implode('|',$books2);//收藏的书
        }else{
            $BookName3=null;
        }

        if($model->f_author){
            //通过作者id遍历查询作者名
            $author_ids=explode('|',$model->f_author);//分割喜欢的作者为数组
            $author_ids=array_filter($author_ids);//删除数组中空元素

            $names=[];
            foreach ($author_ids as $author_id){
                $author=Author::findBySql("SELECT id,name FROM author where id=$author_id")->one();
                $names[$author->id]=$author->name;
            }
            $AuthorName=implode('|',$names);
        }else{
            $AuthorName=null;
        }

        //根据用户id到reading查询该用户读过的书id,再根据书id到book表查询书名
        $book_ids = Reading::findBySql("SELECT book_id FROM reading where user_id=$id ORDER BY `create_time` DESC limit 5")->all();

        //遍历查询书名
        if($book_ids){
            $books =[];//定义空数组装书名
            foreach ($book_ids as $book_id) {
                $book= Book::findBySql("SELECT id,name FROM book where id=$book_id->book_id")->one();
                $books[$book->id]=$book->name;//将书名装入数组中
            }
            $BookName=implode('|',$books);//分割数组成字符串(读过的书)
        }else{
            $BookName=null;
        }



        //查询用户已购买的书
        $purchaseds=Purchased::find()->where(['user_id'=>$id])->limit(5)->all();
        if($purchaseds){
            $bookdata=[];

            foreach ($purchaseds as $purchased){
                $book2=Book::findBySql("SELECT id,name FROM book where id=$purchased->book_id")->one();
                $bookdata[$book2->id]=$book2->name;
            }
            $BookName2=implode('|',$bookdata);//购买的书
            //var_dump($bookdata);exit;
        }else{
            $BookName2=null;
        }
        return $this->render('detail', ['model' => $model,'BookName'=>$BookName,'TypeName'=>$TypeName,'AuthorName'=>$AuthorName,'BookName2'=>$BookName2,'BookName3'=>$BookName3]);
    }

    //封禁用户
    public function actionBan(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $user=User::findOne(['id'=>$id]);
        if($user){
            $user->status=0;
            $user->save();
            return 'success';
        }else{
            return 'error';
        }
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