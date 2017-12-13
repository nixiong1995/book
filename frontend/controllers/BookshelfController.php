<?php
namespace frontend\controllers;
use backend\models\Book;
use backend\models\Reading;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

class BookshelfController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionIndex(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[],
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
            $result['msg']= $res;
            }else{
                $user_id=\Yii::$app->request->post('user_id');
                if($user_id){
                    //查询用户读过的书id
                    $ReadIds=\Yii::$app->db->createCommand("SELECT book_id FROM reading WHERE user_id=$user_id")->queryColumn();
                    //根据书id查询书信息
                    $Books1=Book::find()->where(['id'=>$ReadIds])->all();
                    if($Books1){
                        foreach ($Books1 as $book1){
                            $result['data'][]=['book_id'=>$book1->id,'name'=>$book1->name,
                                'category'=>$book1->category->name,'author'=>$book1->author->name,
                                'view'=>$book1->clicks,'image'=>HTTP_PATH.$book1->image,'size'=>$book1->size,
                                'score'=>$book1->score,'intro'=>$book1->intro,'is_end'=>$book1->is_end,
                                'download'=>$book1->downloads,'collection'=>$book1->collection,'author_id'=>$book1->author_id,
                                'category_id'=>$book1->category_id,'no_free'=>$book1->no,'type'=>$book1->type,
                                'create_time'=>$book1->create_time,'update_time'=>$book1->update_time];
                        }
                        $result['code']=200;
                        $result['msg']='获取书信息成功';
                        return $result;
                    }
                }

                //没有收藏过书,默认推荐
                $Books2=Book::find()->orderBy('downloads DESC')->limit(7)->all();
                foreach ( $Books2 as $book2){
                    $result['data'][]=['book_id'=>$book2->id,'name'=>$book2->name,
                        'category'=>$book2->category->name,'author'=>$book2->author->name,
                        'view'=>$book2->clicks,'image'=>HTTP_PATH.$book2->image,'size'=>$book2->size,
                        'score'=>$book2->score,'intro'=>$book2->intro,'is_end'=>$book2->is_end,
                        'download'=>$book2->downloads,'collection'=>$book2->collection,'author_id'=>$book2->author_id,
                        'category_id'=>$book2->category_id,'no_free'=>$book2->no,'type'=>$book2->type,
                        'create_time'=>$book2->create_time,'update_time'=>$book2->update_time];
                }
                $result['code']=200;
                $result['msg']='获取书信息成功';



            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}