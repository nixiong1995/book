<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Author;
use backend\models\Book;
use libs\PostRequest;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\Controller;

class CopyrightController extends Controller{
    //版权方图书列表
    public function actionIndex(){
        $category_id=\Yii::$app->request->get('category');
        $book=\Yii::$app->request->get('book');//书名
        $author=\Yii::$app->request->get('author');//作者
        $is_free=\Yii::$app->request->get('is_free');//是否是收费书
        //查询数据库分类第一位
        if(!$book && !$author && !$is_free){
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
        if($is_free){
            $where.=" and is_free='$is_free'";
        }

        $total1=Book::find()->count('id');//数据库总书数量
        $total2=Book::find()->andWhere(['is_api'=>0])->count('id');//本地书数量
        $total3=Book::find()->andWhere(['ascription'=>2])->count('id');//爬虫图书
        $total4=Book::find()->andWhere(['is_api'=>1])->count('id');//版权图书
        $count=Book::findBySql("SELECT * FROM book WHERE `is_api`=1 $where")->count();
        //实例化分页工具类
        $pager=new Pagination([
            'totalCount'=>$count,//总条数
            'defaultPageSize'=>20,//每页显示条数
        ]);
        $models=Book::findBySql("SELECT * FROM book WHERE `is_api`=1 $where ORDER by create_time DESC lIMIT $pager->offset,$pager->limit")->all();
        //分页查询
        // $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager,'total1'=>$total1,'total2'=>$total2,'total3'=>$total3,'total4'=>$total4]);

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
        //地址栏参数
        $data=\Yii::$app->request->get('data');
        if($data){
            //将数组通过&符号链接
            $s = urldecode(http_build_query($data));
        }
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
                    $model->update_time=time();
                    //保存所有数据
                    $model->save();
                    $transaction->commit();
                    \Yii::$app->session->setFlash('success', '版权书修改成功');

                }catch (Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                }

                return $this->redirect(['copyright/index?'.$s]);
            }
        }
        return $this->render('edit',['model'=>$model]);

    }

    //批量插入版权方图书
    public function actionInsert(){
        $postUrl = 'http://partner.chuangbie.com/partner/booklist';
        $curlPost =['partner_id'=>2130,'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b','page_size'=>100];
        $post=new PostRequest();
        $data=$post->request_post( $postUrl,$curlPost);
        $datas=json_decode($data,true);

        foreach ($datas['content']['data'] as $data){
            $relust=Book::findOne(['name'=>$data['book_name']]);
            if(!$relust){
                $author=Author::findOne(['name'=>$data['author_name']]);
                $author_id='';
                if($author){
                    $author_id=$author->id;
                }else{
                    $author2=new Author();
                    $author2->name=$data['author_name'];
                    $author2->create_time=time();
                    $author2->save(false);
                    $author_id=$author2->id;
                }
                $category_id='';

                if($data['ftype_id']==10){
                    $category_id=29;
                }elseif ($data['ftype_id']==157){
                    $category_id=24;
                }elseif($data['ftype_id']==162){
                    $category_id=30;
                }elseif ($data['ftype_id']==1){
                    $category_id=16;
                }elseif ($data['ftype_id']==2){
                    $category_id=22;
                }elseif ($data['ftype_id']==3){
                    $category_id=28;
                }elseif ($data['ftype_id']==4){
                    $category_id=19;
                }elseif ($data['ftype_id']==5){
                    $category_id=26;
                }elseif ($data['ftype_id']==6){
                    $category_id=18;
                }elseif ($data['ftype_id']==8){
                    $category_id=35;
                }elseif ($data['ftype_id']==9){
                    $category_id=34;
                }elseif ($data['ftype_id']==13){
                    $category_id=30;
                }elseif($data['ftype_id']==149){
                    $category_id=33;
                }elseif($data['ftype_id']==160){
                    $category_id=19;
                }
                \Yii::$app->db->createCommand()->batchInsert(Book::tableName(),
                    [
                        'copyright_book_id',
                        'name',
                        'author_id',
                        'category_id',
                        'from',
                        'ascription',
                        'image',
                        'intro',
                        'is_free',
                        'no',
                        'size',
                        'type',
                        'is_end',
                        'clicks',
                        'score',
                        'collection',
                        'downloads',
                        'price',
                        'last_update_chapter_id',
                        'last_update_chapter_name',
                        'status',
                        'create_time'
                    ],
                    [
                        [
                            $data['book_id'],
                            $data['book_name'],
                            $author_id,
                            $category_id,
                            3,
                            1,
                            $data['cover_url'],
                            $data['description'],
                            2,
                            40,
                            $data['word_count']*2,
                            'txt',
                            $data['status'],
                            rand(5000,10000),
                            rand(7,10),
                            rand(5000,10000),
                            rand(5000,10000),
                            4,
                            $data['last_update_chapter_id'],
                            $data['last_update_chapter_name'],
                            1,
                            time()
                        ],
                    ])->execute();

            }

        }
        echo '批量插入成功';
    }

    //批量更新版权方图书
    public function actionUpdate(){
        $postUrl = 'http://partner.chuangbie.com/partner/booklist';
        $curlPost =['partner_id'=>2130,'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b','page_size'=>100];
        $post=new PostRequest();
        $data=$post->request_post($postUrl,$curlPost);
        $datas=json_decode($data,true);
        foreach ($datas['content']['data'] as $data){
            Book::updateAll(
                ['size'=>$data['word_count']*2,'is_end'=>$data['status'],'last_update_chapter_id'=>$data['last_update_chapter_id'],'last_update_chapter_name'=>$data['last_update_chapter_name'],'update_time'=>time()],
                ['name'=>$data['book_name']]);

        }

        echo '批量更新成功';

    }

    //检测爬虫书和版权书是否重复
    public function actionDetectionLocal(){
        $postUrl = 'http://partner.chuangbie.com/partner/booklist';
        $curlPost =['partner_id'=>2130,'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b','page_size'=>100];
        $post=new PostRequest();
        $data=$post->request_post($postUrl,$curlPost);
        $datas=json_decode($data,true);
        $rows=[];
        foreach ($datas['content']['data'] as $data){
            $book_name=$data['book_name'];
            //查询数据库爬虫书中是否有版权书
            $rows[]=\Yii::$app->db->createCommand("SELECT name FROM book WHERE name='$book_name' AND `from`=4")->queryScalar();

        }
        //删除数组中空元素
        $rows=array_filter($rows);
        var_dump($rows);
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