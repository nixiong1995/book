<?php
namespace frontend\controllers;
use backend\models\Book;
use libs\Verification;
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

}