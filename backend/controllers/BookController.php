<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\Author;
use backend\models\Book;
use backend\models\Category;
use backend\models\Chapter;
use backend\models\GroomForm;
use backend\models\LoginForm;
use libs\Read;
use yii\bootstrap\Html;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\UploadedFile;

class BookController extends Controller{

    //图书列表
    public function actionIndex(){
        $category_id=\Yii::$app->request->get('category');
        $book=\Yii::$app->request->get('book');//书名
        $author=\Yii::$app->request->get('author');//作者
        $is_free=\Yii::$app->request->get('is_free');
        //查询数据库分类第一位
        if(!$book && !$author && $is_free){
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
        $count=Book::findBySql("SELECT * FROM book WHERE `is_api`=0 $where")->count();
        //实例化分页工具类
        $pager=new Pagination([
            'totalCount'=>$count,//总条数
            'defaultPageSize'=>20,//每页显示条数
        ]);
        $models=Book::findBySql("SELECT * FROM book WHERE `is_api`=0 $where ORDER by create_time DESC lIMIT $pager->offset,$pager->limit")->all();
        //分页查询
        // $models=$query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager,'total1'=>$total1,'total2'=>$total2,'total3'=>$total3,'total4'=>$total4]);

    }

    //图书添加
    public function actionAdd(){
                    $model=new Book();
                    $model->scenario=Book::SCENARIO_ADD;
                    $request=\Yii::$app->request;
                    if($request->isPost){
                        $model->load($request->post());
                        $model->file=UploadedFile::getInstance($model,'file');
                        if ($model->validate()) {//验证规则
                            $Author2=Author::findOne(['name'=>$model->author_name]);
                            if($Author2){
                                //数据库有该作者
                                if($model->file){
                                    $dir =UPLOAD_PATH .date("Y").'/'.date("m").'/'.date("d").'/';
                                    if (!is_dir($dir)) {
                                        mkdir($dir,0777,true);
                                    }
                                    $fileName =uniqid() . rand(1, 100000) . '.' . $model->file->extension;
                                    $dir= $dir . "/" . $fileName;
                                    //移动文件
                                    $model->file->saveAs($dir, false);
                                    $uploadSuccessPath = date("Y").'/'.date("m").'/'.date("d").'/' . $fileName;
                                    $model->image = $uploadSuccessPath;
                                }
                                $model->author_id=$Author2->id;
                                $model->status=1;
                                $model->create_time=time();
                                //保存所有数据
                                $model->save();

                                \Yii::$app->session->setFlash('success', '添加成功');
                            }else{
                                //数据库没有该作者
                                $Author=new Author();
                                $Author->name=$model->author_name;
                                $Author->status=1;
                                $Author->create_time=time();
                                $transaction=\Yii::$app->db->beginTransaction();//开启事务
                                try{

                                    $Author->save(false);

                                    if($model->file){
                                        $dir =UPLOAD_PATH .date("Y").'/'.date("m").'/'.date("d").'/';
                                        if (!is_dir($dir)) {
                                            mkdir($dir,0777,true);
                                        }
                                        $fileName = uniqid() . rand(1, 100000) . '.' . $model->file->extension;
                                        $dir= $dir . "/" . $fileName;
                                        //移动文件
                                        $model->file->saveAs($dir, false);
                                        $uploadSuccessPath = date("Y").'/'.date("m").'/'.date("d").'/' . $fileName;
                                        $model->image = $uploadSuccessPath;
                                    }
                                    $model->author_id=$Author->id;
                                    $model->status=1;
                                    $model->create_time=time();
                                    //保存所有数据
                                    $model->save();
                                    $transaction->commit();
                                    \Yii::$app->session->setFlash('success', '添加成功');

                                }catch (Exception $e){
                                    //事务回滚
                                   $transaction->rollBack();
                                }
                            }
                //跳转
               return $this->redirect(['book/index?category='.$model->category_id]);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //图书修改
    public function actionEdit($id)
    {
        //接收地址栏参数
        $data=\Yii::$app->request->get('data');
        if($data){
            //将数组通过&符号链接
            $s = urldecode(http_build_query($data));
        }
        $model = Book::findOne(['id' => $id]);
        $Author=Author::findOne(['id'=>$model->author_id]);
        $model->author_name=$Author->name;
        $model->file = $model->image;//书封面
        $old_path=$model->file;//书封面旧路径
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $model->load($request->post());
            $model->file = UploadedFile::getInstance($model, 'file');//书封面
            if ($model->validate()) {//验证规则

                $Author->name=$model->author_name;
                $transaction=\Yii::$app->db->beginTransaction();//开启事务
                try{
                    //保存作者信息
                    $Author->save(false);
                    //处理书上传封面
                    if($model->file){
                        $dir =UPLOAD_PATH .date("Y").'/'.date("m").'/'.date("d").'/';
                        if (!is_dir($dir)) {
                            mkdir($dir,0777,true);
                        }
                        $fileName = uniqid() . rand(1, 100000) . '.' . $model->file->extension;
                        $dir= $dir . "/" . $fileName;
                        //移动文件
                        $model->file->saveAs($dir, false);
                        $uploadSuccessPath = date("Y").'/'.date("m").'/'.date("d").'/' . $fileName;
                        $model->image = $uploadSuccessPath;
                        //如果有旧文件,删除旧文件
                        if($old_path){
                            $old_path=UPLOAD_PATH.$old_path;
                            unlink($old_path);//删除原文件
                        }
                    }
                    $model->author_id=$Author->id;
                    //保存所有数据
                    $model->save();
                    $transaction->commit();
                    \Yii::$app->session->setFlash('success', '修改成功');

                }catch (Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                }
                //跳转
                return $this->redirect(['book/index?'.$s]);
            }
        }
        return $this->render('add', ['model' => $model]);
    }

    //图书删除
    public function actionDel(){
        //接收id
        $id=\Yii::$app->request->post('id');
        $book=Book::findOne(['id'=>$id]);
        $author_id=$book->author_id;//作者id
        $path=$book->image;
        $transaction=\Yii::$app->db->beginTransaction();//开启事务
        try{
            //删除书
            $book->delete();
            if($path){
                $path=UPLOAD_PATH.$path;
                unlink($path);
            }

            //删除作者(判断该作者是否还有其他书籍)
            $relust=Book::findOne(['author_id'=>$author_id]);
            if(!$relust){
                $author=Author::findOne(['id'=>$author_id]);
                //作者照片
                $path3=$author->image;
                $author->delete();
                if($path3){
                    $path3=UPLOAD_PATH.$path3;
                    unlink($path3);
                }
            }

            //删除该书的所有章节
            $chapters=Chapter::find()->where(['book_id'=>$id])->all();
            foreach ($chapters as $chapter){
                $path2=$chapter->path;//章节文件路径
                $chapter->delete();
                if($path2){
                    $path2=BOOK_PATH.$path2;
                    unlink($path2);
                }
            }
            $transaction->commit();
            return 'success';
        }catch (Exception $e){
            //事务回滚
            $transaction->rollBack();

        }
    }

    //图书详情
    public function actionDetails(){
        $book_id=\Yii::$app->request->get('book_id');
        $model=Book::find()->where(['id'=>$book_id])->one();
        return $this->render('details',['model'=>$model]);

    }

    //加入分类精选
    public function actionSelected(){
        $id=\Yii::$app->request->post('id');
        $book=Book::findOne(['id'=>$id]);
        if($book){
            $book->groom_time=time();
            $book->groom=7;
            $book->save(false);
            return 'success';
        }else{
            return 'error';
        }
    }

    //加入推荐
    public function actionGroom(){
        $book_id=\Yii::$app->request->get('book_id');
        $model=new GroomForm();
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());

            if($model->validate()){

                $book=Book::findOne(['id'=>$book_id]);
                $book->groom=$model->groom;
                $book->groom_time=time();
                $book->save(false);
                \Yii::$app->session->setFlash('success', '添加推荐成功');
                return $this->redirect(['book/groom-index']);


            }
        }
        return $this->render('groom-add',['model'=>$model]);
    }

    //推荐列表
    public function actionGroomIndex(){
        //接收参数
        $type=\Yii::$app->request->get('type');
        switch ($type)
        {
            case 1:
                $models=Book::find()->where(['groom'=>1])->orderBy('groom_time DESC')->limit(5)->all();
                break;
            case 2:
                $models=Book::find()->where(['groom'=>2])->orderBy('groom_time DESC')->limit(3)->all();
                break;
            case 3:
                $models=Book::find()->where(['groom'=>3])->orderBy('groom_time DESC')->limit(8)->all();
                break;
            case 4:
                $models=Book::find()->where(['groom'=>4])->orderBy('groom_time DESC')->limit(3)->all();
                break;
            case 5:
                $models=Book::find()->where(['groom'=>5])->orderBy('groom_time DESC')->limit(6)->all();
                break;
            case 6:
                $models=Book::find()->where(['groom'=>6])->orderBy('groom_time DESC')->limit(6)->all();
                break;
            case 8:
                $models=Book::find()->where(['groom'=>8])->orderBy('groom_time DESC')->limit(10)->all();
                break;
            case 9:
                $models=Book::find()->where(['groom'=>9])->orderBy('groom_time DESC')->limit(10)->all();
                break;
            case 10:
                $models=Book::find()->where(['groom'=>10])->orderBy('groom_time DESC')->limit(10)->all();
                break;
            case 11:
                $models=Book::find()->where(['groom'=>11])->orderBy('groom_time DESC')->limit(10)->all();
                break;
            default:
                $models=Book::find()->where(['groom'=>1])->orderBy('groom_time DESC')->limit(5)->all();
        }
        return $this->render('groom-index',['models'=>$models]);
    }

    //精品推荐列表
    public function actionBoutique(){
        //接收分类id
        $category_id=\Yii::$app->request->get('category');
        //查询推荐分类前1的分类id
        $id=\Yii::$app->db->createCommand("SELECT id FROM category ORDER BY groom_time DESC ")->queryScalar();
        $category_id=$category_id?$category_id:$id;
        $models=Book::find()->where(['category_id'=>$category_id ,'groom'=>7])->orderBy('groom_time desc')->limit(3)->all();
        return $this->render('boutique',['models'=>$models]);


    }

   //批量修改分类
    public function actionUpdate(){
        //接收书id和分类id
        $book_id=\Yii::$app->request->post('book_id');
        $category_id=\Yii::$app->request->post('category_id');
        //查找书,修改书分类id
        $res=Book::updateAll(['category_id'=>$category_id],['id'=>$book_id]);
        if($res){
            return 'success';
        }else{
            return 'error';
        }
    }

    //取消图书推荐
    public function actionGroomUpdate(){
        $id=\Yii::$app->request->post('id');
        $model=Book::findOne(['id'=>$id]);
        $model->groom=(Null);
        $model->groom_time=(Null);
        if($model->save()){
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