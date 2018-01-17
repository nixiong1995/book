<?php
namespace frontend\controllers;
use backend\models\Book;
use backend\models\Reading;
use backend\models\UserDetails;
use libs\Verification;
use yii\db\Exception;
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
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
           $result['msg']= $res;
            }else{
                $user_id=\Yii::$app->request->post('user_id');
                if($user_id){
                    //查询用户收藏过的书id
                    $collect=\Yii::$app->db->createCommand("SELECT collect FROM user_details WHERE user_id=$user_id")->queryScalar();
                    if($collect){

                        //将收藏过的书分割成数组
                        $CollectIds=explode('|',$collect);
                        //去除数组中空元素
                        $CollectIds=array_filter($CollectIds);
                        //根据书id查询书信息
                        $Books1=Book::find()->where(['id'=>$CollectIds])->all();
                        if($Books1){
                            foreach ($Books1 as $book1){
                                //判断是否版权图书,不拼接图片域名
                                $ImgUrl=$book1->image;
                                if($book1->from!=3){
                                    $ImgUrl=HTTP_PATH.$ImgUrl;
                                }
                                $result['data'][]=['book_id'=>$book1->id,'name'=>$book1->name,
                                    'category'=>$book1->category->name,'author'=>$book1->author->name,
                                    'view'=>$book1->clicks,'image'=>$ImgUrl,'size'=>$book1->size,
                                    'score'=>$book1->score,'intro'=>$book1->intro,'is_end'=>$book1->is_end,
                                    'download'=>$book1->downloads,'collection'=>$book1->collection,'author_id'=>$book1->author_id,
                                    'category_id'=>$book1->category_id,'no_free'=>$book1->no,'type'=>$book1->type,
                                    'create_time'=>$book1->create_time,'update_time'=>$book1->update_time,'from'=>$book1->from,
                                    'is_free'=>$book1->is_free,'price'=>$book1->price,'search'=>$book1->search,'sale'=>$book1->search,
                                    'ascription_name'=>$book1->information->name,'ascription_id'=>$book1->ascription,
                                    'copyright_book_id'=>$book1->copyright_book_id,'last_update_chapter_id'=>$book1->last_update_chapter_id,
                                    'last_update_chapter_name'=>$book1->last_update_chapter_name];
                            }
                            $result['code']=200;
                            $result['msg']='获取收藏书信息成功';
                            return $result;
                        }
                    }else{
                        //没有收藏过书,默认推荐(查询推荐书id)
                        //判断用户是否阅读过书,阅读过书不在推荐
                        $model=Reading::find()->where(['user_id'=>$user_id])->one();
                        if(!$model){
                            $GroomIds=\Yii::$app->db->createCommand('SELECT id FROM book WHERE id >= ((SELECT MAX(id) FROM book)-(SELECT MIN(id) FROM book)) * RAND() + (SELECT MIN(id) FROM book)  LIMIT 7')->queryColumn();
                            $Books2=Book::find()->where(['id'=>$GroomIds])->all();
                            //将推荐的书的通过|符号转成字符串存入数据库
                            $collect=implode('|',$GroomIds);
                            $model->collect=$collect;
                            $model->save();
                            foreach ( $Books2 as $book2){
                                //判断是否版权图书,不拼接图片域名
                                $ImgUrl=$book2->image;
                                if($book2->from!=3){
                                    $ImgUrl=HTTP_PATH.$ImgUrl;
                                }
                                $result['data'][]=['book_id'=>$book2->id,'name'=>$book2->name,
                                    'category'=>$book2->category->name,'author'=>$book2->author->name,
                                    'view'=>$book2->clicks,'image'=>$ImgUrl,'size'=>$book2->size,
                                    'score'=>$book2->score,'intro'=>$book2->intro,'is_end'=>$book2->is_end,
                                    'download'=>$book2->downloads,'collection'=>$book2->collection,'author_id'=>$book2->author_id,
                                    'category_id'=>$book2->category_id,'no_free'=>$book2->no,'type'=>$book2->type,
                                    'create_time'=>$book2->create_time,'update_time'=>$book2->update_time,'from'=>$book2->from,
                                    'is_free'=>$book2->is_free,'price'=>$book2->price,'search'=>$book2->search,'sale'=>$book2->search,
                                    'ascription_name'=>$book2->information->name,'ascription_id'=>$book2->ascription,
                                    'copyright_book_id'=>$book2->copyright_book_id,'last_update_chapter_id'=>$book2->last_update_chapter_id,
                                    'last_update_chapter_name'=>$book2->last_update_chapter_name];
                            }
                            $result['code']=200;
                            $result['msg']='获取默认推荐书信息成功';
                        }else{
                            $result['code']=200;
                            $result['msg']='您的书架为空';
                        }
                    }
                }else{
                    $result['msg']='请传入用户id';
                }
           }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //删除书架的书
    public function actionDel(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
               $result['msg']= $res;
           }else{
                $user_id=\Yii::$app->request->post('user_id');
                $book_id=\Yii::$app->request->post('book_id');
                //查询用户详情收藏过的书
                $model=UserDetails::findOne(['user_id'=>$user_id]);
                if($model){
                    //将收藏过的书转成数组
                    $CollectIds=explode('|',$model->collect);
                    //查询该书id在数组中的键名
                    $key=array_search($book_id,$CollectIds);
                    if($key!==false){
                        //删除数组中的书id
                        $res1=array_splice($CollectIds, $key, 1);
                        if($res1){
                            //将数组通过|分割成字符串保存进数据库
                            $collect=implode('|',$CollectIds);
                            $model->collect=$collect;
                            $model->save();
                            $result['code']=200;
                            $result['msg']='删除书籍成功';

                        }else{
                            $result['msg']='删除书籍失败';
                        }
                    }else{
                        $result['msg']='用户未收藏该书';
                    }


                }else{
                    $result['msg']='未找到该用户';
                }
           }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //添加书架书籍
    public function actionAdd(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
          if($res){
             $result['msg']= $res;
          }else{
                $book_id=\Yii::$app->request->post('book_id');
                $user_id=\Yii::$app->request->post('user_id');
                $model=UserDetails::findOne(['user_id'=>$user_id]);
                if($model){
                    //将收藏过的书转成数组
                    $CollectIds=explode('|',$model->collect);
                    $CollectIds=array_filter($CollectIds);//删除数组中空元素
                    //查询该书id在数组中的键名
                    $key=array_search($book_id,$CollectIds);
                    if($key!==false){
                        $result['msg']='您已经加入过了';
                    }else{
                        $res1=array_push($CollectIds,$book_id);//将书id加在数组中
                        if($res1){
                            $collect=implode('|',$CollectIds);
                            $model->collect=$collect;
                            $transaction=\Yii::$app->db->beginTransaction();//开启事务
                            try{
                                $model->save();
                                $book=Book::findOne(['id'=>$book_id]);
                                $book->collection=$book->collection+1;
                                $book->save();
                                $transaction->commit();

                            }catch (Exception $e){
                                //事务回滚
                                $transaction->rollBack();
                            }

                            $result['code']=200;
                            $result['msg']='收藏书籍成功';
                        }else{
                            $result['msg']='收藏书籍失败';
                        }
                    }
                }else{
                    $result['msg']='未找到该用户';
                }
          }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}