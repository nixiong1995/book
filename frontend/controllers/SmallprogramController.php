<?php
namespace frontend\controllers;
use backend\models\Book;
use libs\Verification;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\Response;

//微信小程序
class SmallprogramController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //推荐图书列表
    public function actionGroom(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                $relust['msg']= $res;
            }else{
                //接收参数
                $category_id=\Yii::$app->request->post('category_id');
                if(empty($category_id)){
                    $relust='未传入指定参数';
                    return $relust;
                }
                $books=Book::find()->where(['category_id'=>$category_id])->andWhere(['<>','from',4])->orderBy('update_time DESC')->limit(12)->all();
                if($books){
                    foreach ($books as $book){
                        $relust['data'][]=['book_id'=>$book->id,'name'=>$book->name,
                            'category'=>$book->category->name,'author'=>$book->author->name,
                            'view'=>$book->clicks,'image'=>$book->image,'size'=>$book->size,
                            'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                            'download'=>$book->downloads,'collection'=>$book->collection,'author_id'=>$book->author_id,
                            'category_id'=>$book->category_id,'no_free'=>$book->no,'type'=>$book->type,
                            'create_time'=>$book->create_time,'update_time'=>$book->update_time,'from'=>$book->from,
                            'is_free'=>$book->is_free,'price'=>$book->price,'search'=>$book->search,'sale'=>$book->search,
                            'ascription_name'=>$book->information->name,'ascription_id'=>$book->ascription,
                            'copyright_book_id'=>$book->copyright_book_id,'last_update_chapter_id'=>$book->last_update_chapter_id,
                            'last_update_chapter_name'=>$book->last_update_chapter_name];
                    }
                    $relust['code']=200;
                    $relust['msg']='成功返回图书';
                }else{
                    $relust['msg']='没有数据';
                }
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //分类
    public function actionCategory(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isGet){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
             $result['msg']= $res;
            }else{
            $category_id=\Yii::$app->request->get('category_id');
            $page=\Yii::$app->request->get('page');
            $type=\Yii::$app->request->get('type');
            $query=Book::find()->where(['category_id'=>$category_id])->andWhere(['<>','from',4]);
            $count=ceil($query->count()/10);
            if($page>$count){
                $result['msg']='没有更多了';
                return $result;
            }
            $pager=new Pagination([
                'totalCount'=>$query->count(),
                'defaultPageSize'=>10,
            ]);
            if($type==1){
                $models=$query->limit($pager->limit)->offset($pager->offset)->orderBy('clicks DESC')->all();
            }elseif ($type==2){
                $models=$query->limit($pager->limit)->offset($pager->offset)->orderBy('create_time DESC')->all();
            }elseif($type==3){
                $models=$query->limit($pager->limit)->offset($pager->offset)->orderBy('score DESC')->all();
            }

            foreach ($models as $model){
                //判断是否版权图书,不是拼接图片域名
                $ImgUrl=$model->image;
                if($model->from!=3){
                    $ImgUrl=HTTP_PATH.$ImgUrl;
                }
                $result['data'][]=['book_id'=>$model->id,'name'=>$model->name,
                    'category'=>$model->category->name,'author'=>$model->author->name,
                    'view'=>$model->clicks,'image'=>$ImgUrl,'size'=>$model->size,
                    'score'=>$model->score,'intro'=>$model->intro,'is_end'=>$model->is_end,
                    'download'=>$model->downloads,'collection'=>$model->collection,'author_id'=>$model->author_id,
                    'category_id'=>$model->category_id,'no_free'=>$model->no,'type'=>$model->type,
                    'create_time'=>$model->create_time,'update_time'=>$model->update_time,'from'=>$model->from,
                    'is_free'=>$model->is_free,'price'=>$model->price,'search'=>$model->search,'sale'=>$model->search,
                    'ascription_name'=>$model->information->name,'ascription_id'=>$model->ascription,
                    'copyright_book_id'=>$model->copyright_book_id,'last_update_chapter_id'=>$model->last_update_chapter_id,
                    'last_update_chapter_name'=>$model->last_update_chapter_name];
            }
            $result['code']=200;
            $result['msg']='获取分类成功';
             }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}