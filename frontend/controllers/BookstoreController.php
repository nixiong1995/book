<?php
namespace frontend\controllers;
use backend\models\Advert;
use backend\models\Author;
use backend\models\Book;
use backend\models\Category;
use backend\models\Reading;
use backend\models\Seckill;
use backend\models\UserDetails;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

class BookstoreController extends Controller{

    public $enableCsrfValidation=false;
    public $token = 'yuekukuyue666888';

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //获取广告
    public function actionAdvert(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[],
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
          //  if($res){
              //  $result['msg']= $res;
           // }else{
                $position=\Yii::$app->request->post('position');
                $models=Advert::find()->where(['position'=>$position])->orderBy('create_time DESC')->limit(3)->all();
                //var_dump($models);exit;
                foreach ($models as $model){
                    $result['data']['advert'][]=['position'=>$model->position ,'sort'=>$model->sort,'image'=>HTTP_PATH.$model->image];
                }
                $result['code']=200;
                $result['msg']='获取广告图成功';
           // }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //今日必读,限时秒杀,猜你喜欢
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
            //今日必读
            $models1=Book::find()->where(['groom'=>1])->orderBy('groom_time DESC')->limit(5)->all();
                //var_dump($models1);exit;
            foreach ($models1 as $model1){
                $result['data']['read-today'][]=['book_id'=>$model1->id,'name'=>$model1->name,
                    'category'=>$model1->category->name,'author'=>$model1->author->name,
                    'view'=>$model1->clicks,'image'=>HTTP_PATH.$model1->image,'size'=>$model1->size,
                    'score'=>$model1->score,'intro'=>$model1->intro,'is_end'=>$model1->is_end,
                    'download'=>$model1->downloads,'collection'=>$model1->collection];
            }
            //var_dump($result);exit;
            //限时秒杀
            $models2=Seckill::find()->orderBy('create_time DESC')->limit(4)->all();
            foreach ($models2 as $model2){
                $categoty=Category::findOne(['id'=>$model2->book->category_id]);
                $author=Author::findOne(['id'=>$model2->book->author_id]);
                $result['data']['seckill'][]=['book_id'=>$model2->book->id,'name'=>$model2->book->name,
                    'category'=> $categoty->name,'author'=>$author->name,
                    'view'=>$model2->book->clicks,'image'=>HTTP_PATH.$model2->book->image,'size'=>$model2->book->size,
                    'score'=>$model2->book->score,'intro'=>$model2->book->intro,'is_end'=>$model2->book->is_end,
                    'download'=>$model2->book->downloads,'collection'=>$model2->book->collection,];
            }


            //猜你喜欢
            $user_id=\Yii::$app->request->post('user_id');
            //根据用户id查找喜欢的类型
            if($user_id){
                //是注册用户
                $userdetail=UserDetails::findOne(['user_id'=>$user_id]);
                if($userdetail->f_type){
                    //有自己喜欢的类型
                    $category_ids=explode('|',$userdetail->f_type);
                    $index=array_rand($category_ids);
                    //遍历查询书
                    $books=Book::find()->where(['category_id'=>$category_ids[$index]])->orderBy('score DESC')->limit(3)->all();
                   foreach ($books as $book){
                       $result['data']['like'][]=['book_id'=>$book->id,'name'=>$book->name,
                           'category'=>$book->category->name,'author'=>$book->author->name,
                           'view'=>$book->clicks,'image'=>HTTP_PATH.$book->image,'size'=>$book->size,
                           'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                           'download'=>$book->downloads,'collection'=>$book->collection];
                   }
                   $result['code']=200;
                    $result['msg']='获取书城信息成功';

                }
                return  $result;

            }
            //没有注册以及没有喜欢的类型
            $ids=[];
            $categorys=Category::findBySql("select id from category")->all();
            foreach ($categorys as $category){
                $ids[$category->id]=$category->id;
            }
            $index2=array_rand($ids);
            $books=Book::find()->where(['category_id'=>$index2])->orderBy('score DESC')->limit(3)->all();
            foreach ($books as $book){
                $result['data']['like'][]=['book_id'=>$book->id,'name'=>$book->name,
                    'category'=>$book->category->name,'author'=>$book->author->name,
                    'view'=>$book->clicks,'image'=>HTTP_PATH.$book->image,'size'=>$book->size,
                    'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                    'download'=>$book->downloads,'collection'=>$book->collection];
            }
            $result['code']=200;
            $result['msg']='获取书城信息成功';
           }

        }else{
           $result['msg']='请求方式错误';
       }
        return $result;
    }

    //分类接口
    public function actionCategory(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[]
        ];
        if (\Yii::$app->request->post()){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
               $result['msg']= $res;
           }else{
            //查询男频分类
                $categorys1=Category::find()->where(['type'=>1])->all();
                foreach ($categorys1 as $category){
                    $image=\Yii::$app->db->createCommand("select image from book WHERE category_id=$category->id")->queryScalar();
                    $count1=Book::find()->andWhere(['category_id'=>$category->id])->count('id');//查询分类书的数量
                    $result['data']['male'][]=['name'=>$category->name,'intro'=>$category->intro,
                        'status'=>$category->status,'count'=>$category->count,'type'=>$category->type,
                    'image'=>HTTP_PATH.$image,'category_id'=>$category->id,'count'=>$count1];
                }
                //查询女频
            $categorys2=Category::find()->where(['type'=>0])->all();
            foreach ($categorys2 as $category){
                $image=\Yii::$app->db->createCommand("select image from book WHERE category_id=$category->id")->queryScalar();
                $count2=Book::find()->andWhere(['category_id'=>$category->id])->count('id');//查询分类书的数量
                $result['data']['female'][]=['name'=>$category->name,'intro'=>$category->intro,
                    'status'=>$category->status,'count'=>$category->count,'type'=>$category->type,
                    'image'=>HTTP_PATH.$image,'category_id'=>$category->id,'count'=>$count2];
            }
            $result['code']=200;
            $result['msg']='获取分类信息成功';
           }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //图书详情推荐
    public function  actionDetailGroom(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[]
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                $result['msg']= $res;
           }else{
                $category_id=\Yii::$app->request->post('category_id');
                $author_id=\Yii::$app->request->post('author_id');
                //查找同类书
                $books1=Book::find()->where(['category_id'=>$category_id])->orderBy('score DESC')->limit(3)->all();
                foreach ($books1 as $book1){
                    $result['data']['similar'][]=['book_id'=>$book1->id,'name'=>$book1->name,
                        'category'=>$book1->category->name,'author'=>$book1->author->name,
                        'view'=>$book1->clicks,'image'=>HTTP_PATH.$book1->image,'size'=>$book1->size,
                        'score'=>$book1->score,'intro'=>$book1->intro,'is_end'=>$book1->is_end,
                        'download'=>$book1->downloads,'collection'=>$book1->collection];
                }
                //查找作者图书推荐
                $books2=Book::find()->where(['author_id'=>$author_id])->orderBy('score DESC')->limit(3)->all();
                foreach ($books2 as $book2){
                    $result['data']['author'][]=['book_id'=>$book2->id,'name'=>$book2->name,
                        'category'=>$book2->category->name,'author'=>$book2->author->name,
                        'view'=>$book2->clicks,'image'=>HTTP_PATH.$book2->image,'size'=>$book2->size,
                        'score'=>$book2->score,'intro'=>$book2->intro,'is_end'=>$book2->is_end,
                        'download'=>$book2->downloads,'collection'=>$book2->collection];
                }
                $result['code']=200;
                $result['msg']='获取图书详情推荐书籍成功';


           }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

}