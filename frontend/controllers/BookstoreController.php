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
use yii\data\Pagination;
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
         if($res){
               $result['msg']= $res;
          }else{
                $position=\Yii::$app->request->post('position');
                $models=Advert::find()->where(['position'=>$position])->orderBy('create_time DESC')->limit(4)->all();
                //var_dump($models);exit;
                foreach ($models as $model){
                    $result['data'][]=['position'=>$model->position ,'sort'=>$model->sort,'image'=>HTTP_PATH.$model->image];
                }
                $result['code']=200;
                $result['msg']='获取广告图成功';
            }

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
          // if($res){
             //  $result['msg']= $res;
           // }else{
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

            //限时秒杀
            $models2=Seckill::find()->orderBy('create_time DESC')->limit(4)->all();
            foreach ($models2 as $model2){
                $categoty=Category::findOne(['id'=>$model2->book->category_id]);
                $author=Author::findOne(['id'=>$model2->book->author_id]);
                $result['data']['seckill'][]=['book_id'=>$model2->book->id,'name'=>$model2->book->name,
                    'category'=> $categoty->name,'author'=>$author->name,
                    'view'=>$model2->book->clicks,'image'=>HTTP_PATH.$model2->book->image,'size'=>$model2->book->size,
                    'score'=>$model2->book->score,'intro'=>$model2->book->intro,'is_end'=>$model2->book->is_end,
                    'download'=>$model2->book->downloads,'collection'=>$model2->book->collection,'begin_time'=>$model2->begin_time,
                    'end_time'=>$model2->end_time,'people'=>$model2->people];
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
          // }

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

    //分类二级页面接口
    public function actionTwoCategory(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[]
        ];
        if(\Yii::$app->request->isGet){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                $result['msg']= $res;
            }else{
                $category_id=\Yii::$app->request->get('category_id');
                $page=\Yii::$app->request->get('page');
                $query=Book::find()->where(['category_id'=>$category_id]);
                $pager=new Pagination([
                    'totalCount'=>$query->count(),
                    'defaultPageSize'=>10,
                ]);
            $models1=$query->limit($pager->limit)->offset($pager->offset)->orderBy('clicks DESC')->all();
            $models2=$query->limit($pager->limit)->offset($pager->offset)->orderBy('create_time DESC')->all();
            $models3=$query->limit($pager->limit)->offset($pager->offset)->orderBy('score DESC')->all();
            foreach ($models1 as $model1){
                $result['data']['hot'][]=['book_id'=>$model1->id,'name'=>$model1->name,
                    'category'=>$model1->category->name,'author'=>$model1->author->name,
                    'view'=>$model1->clicks,'image'=>HTTP_PATH.$model1->image,'size'=>$model1->size,
                    'score'=>$model1->score,'intro'=>$model1->intro,'is_end'=>$model1->is_end,
                    'download'=>$model1->downloads,'collection'=>$model1->collection];
            }
            foreach ($models2 as $model2){
                $result['data']['new'][]=['book_id'=>$model2->id,'name'=>$model2->name,
                    'category'=>$model2->category->name,'author'=>$model2->author->name,
                    'view'=>$model2->clicks,'image'=>HTTP_PATH.$model2->image,'size'=>$model2->size,
                    'score'=>$model2->score,'intro'=>$model2->intro,'is_end'=>$model2->is_end,
                    'download'=>$model2->downloads,'collection'=>$model2->collection];
            }
            foreach ($models3 as $model3){
                $result['data']['evaluate'][]=['book_id'=>$model3->id,'name'=>$model3->name,
                    'category'=>$model3->category->name,'author'=>$model3->author->name,
                    'view'=>$model3->clicks,'image'=>HTTP_PATH.$model3->image,'size'=>$model3->size,
                    'score'=>$model3->score,'intro'=>$model3->intro,'is_end'=>$model3->is_end,
                    'download'=>$model3->downloads,'collection'=>$model3->collection];
            }
            $result['code']=200;
            $result['msg']='获取分类二级页面成功';
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

    //获取一个本书的详细信息
    public function actionBook(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[]
        ];
        if(\Yii::$app->request->post()){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                $result['msg']= $res;
           }else{
                $book_id=\Yii::$app->request->post('book_id');
                $book=Book::findOne(['id'=>$book_id]);
                    $result['data'][]=['book_id'=>$book->id,'name'=>$book->name,
                        'category'=>$book->category->name,'author'=>$book->author->name,
                        'view'=>$book->clicks,'image'=>HTTP_PATH.$book->image,'size'=>$book->size,
                        'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                        'download'=>$book->downloads,'collection'=>$book->collection];
                    $result['code']=200;
                    $result['msg']='获取图书信息成功';
           }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //书城搜索
    public function actionSearch(){
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
                $keyword=\Yii::$app->request->post('keyword');
                $author_id=\Yii::$app->db->createCommand("select id from author where name='$keyword'")->queryScalar();
                if($author_id){
                    $where="author_id=$author_id";
                }else{
                    $where="name like '%$keyword%'";
                }
                $books=Book::findBySql("SELECT * FROM book WHERE $where")->all();
                foreach ($books as $book){
                    $result['data'][]=['book_id'=>$book->id,'name'=>$book->name,
                        'category'=>$book->category->name,'author'=>$book->author->name,
                        'view'=>$book->clicks,'image'=>HTTP_PATH.$book->image,'size'=>$book->size,
                        'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                        'download'=>$book->downloads,'collection'=>$book->collection];
                    $result['code']=200;
                    $result['msg']='获取图书信息成功';
                }
           }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //书城精品
    public function actionBoutique(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[]
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
           // if($res){
              //  $result['msg']= $res;
           // }else{
                $categorys=Category::find()->orderBy('groom_time DESC')->limit(5)->all();

                foreach ($categorys as $category){
                    $ids[]=$category->id;
                }
                //var_dump($ids);exit;
                $models1=Book::find()->where(['category_id'=>$ids[0]])->orderBy('groom_time DESC')->limit(3)->all();
                foreach ($models1 as $model1){
                    $result['data']['category1'][]=['book_id'=>$model1->id,'name'=>$model1->name,
                        'category'=>$model1->category->name,'author'=>$model1->author->name,
                        'view'=>$model1->clicks,'image'=>HTTP_PATH.$model1->image,'size'=>$model1->size,
                        'score'=>$model1->score,'intro'=>$model1->intro,'is_end'=>$model1->is_end,
                        'download'=>$model1->downloads,'collection'=>$model1->collection];
                }

            $models2=Book::find()->where(['category_id'=>$ids[1]])->orderBy('groom_time DESC')->limit(3)->all();
            foreach ($models2 as $model2){
                $result['data']['category2'][]=['book_id'=>$model2->id,'name'=>$model2->name,
                    'category'=>$model2->category->name,'author'=>$model2->author->name,
                    'view'=>$model2->clicks,'image'=>HTTP_PATH.$model2->image,'size'=>$model2->size,
                    'score'=>$model2->score,'intro'=>$model2->intro,'is_end'=>$model2->is_end,
                    'download'=>$model2->downloads,'collection'=>$model2->collection];
            }

            $models3=Book::find()->where(['category_id'=>$ids[2]])->orderBy('groom_time DESC')->limit(3)->all();
            foreach ($models3 as $model3){
                $result['data']['category3'][]=['book_id'=>$model3->id,'name'=>$model3->name,
                    'category'=>$model3->category->name,'author'=>$model3->author->name,
                    'view'=>$model3->clicks,'image'=>HTTP_PATH.$model3->image,'size'=>$model3->size,
                    'score'=>$model3->score,'intro'=>$model3->intro,'is_end'=>$model3->is_end,
                    'download'=>$model3->downloads,'collection'=>$model3->collection];
            }

            $models4=Book::find()->where(['category_id'=>$ids[3]])->orderBy('groom_time DESC')->limit(3)->all();
            foreach ($models4 as $model4){
                $result['data']['category4'][]=['book_id'=>$model4->id,'name'=>$model4->name,
                    'category'=>$model4->category->name,'author'=>$model4->author->name,
                    'view'=>$model4->clicks,'image'=>HTTP_PATH.$model4->image,'size'=>$model4->size,
                    'score'=>$model4->score,'intro'=>$model4->intro,'is_end'=>$model4->is_end,
                    'download'=>$model4->downloads,'collection'=>$model4->collection];
            }

            $models5=Book::find()->where(['category_id'=>$ids[4]])->orderBy('groom_time DESC')->limit(3)->all();
            foreach ($models5 as $model5){
                $result['data']['category5'][]=['book_id'=>$model5->id,'name'=>$model5->name,
                    'category'=>$model5->category->name,'author'=>$model5->author->name,
                    'view'=>$model5->clicks,'image'=>HTTP_PATH.$model5->image,'size'=>$model5->size,
                    'score'=>$model5->score,'intro'=>$model5->intro,'is_end'=>$model5->is_end,
                    'download'=>$model5->downloads,'collection'=>$model5->collection];
            }
            $result['code']=200;
            $result['msg']='获取信息成功';

          //  }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //书城免费
    public function actionFree(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[]
        ];
        if(\Yii::$app->request->isPost){
           // $obj=new Verification();
            //$res=$obj->check();
            //if($res){
            //  $result['msg']= $res;
            //}else{
            //今日限免
                $books1=Book::find()->where(['groom'=>2])->orderBy('groom_time DESC')->limit(3)->all();
                foreach ( $books1 as $book1){
                    $result['data']['today-free'][]=['book_id'=>$book1->id,'name'=>$book1->name,
                        'category'=>$book1->category->name,'author'=>$book1->author->name,
                        'view'=>$book1->clicks,'image'=>HTTP_PATH.$book1->image,'size'=>$book1->size,
                        'score'=>$book1->score,'intro'=>$book1->intro,'is_end'=>$book1->is_end,
                        'download'=>$book1->downloads,'collection'=>$book1->collection];
                }

                //女生限免
                $books2=Book::find()->where(['groom'=>3])->orderBy('groom_time DESC')->limit(8)->all();
                foreach ( $books2 as $book2){
                    $result['data']['female-free'][]=['book_id'=>$book2->id,'name'=>$book2->name,
                        'category'=>$book2->category->name,'author'=>$book2->author->name,
                        'view'=>$book2->clicks,'image'=>HTTP_PATH.$book2->image,'size'=>$book2->size,
                        'score'=>$book2->score,'intro'=>$book2->intro,'is_end'=>$book2->is_end,
                        'download'=>$book2->downloads,'collection'=>$book2->collection];
                }

                //男生限免
                $books3=Book::find()->where(['groom'=>4])->orderBy('groom_time DESC')->limit(3)->all();
                foreach ( $books3 as $book3){
                    $result['data']['male-free'][]=['book_id'=>$book3->id,'name'=>$book3->name,
                        'category'=>$book3->category->name,'author'=>$book3->author->name,
                        'view'=>$book3->clicks,'image'=>HTTP_PATH.$book3->image,'size'=>$book3->size,
                        'score'=>$book3->score,'intro'=>$book3->intro,'is_end'=>$book3->is_end,
                        'download'=>$book3->downloads,'collection'=>$book3->collection];
                }
                $result['msg']='获取书本信息成功';
                $result['code']=200;
           // }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

}