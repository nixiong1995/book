<?php
namespace frontend\controllers;
use backend\models\Advert;
use backend\models\Author;
use backend\models\Book;
use backend\models\Category;
use backend\models\Chapter;
use backend\models\Reading;
use backend\models\Seckill;
use backend\models\UserDetails;
use frontend\models\Word;
use libs\PostRequest;
use libs\Verification;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\Response;
header("Access-Control-Allow-Origin: *");
class BookstoreController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //获取广告banna图
    public function actionAdvert(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
           // $obj=new Verification();
            //$res=$obj->check();
           //if($res){
                 //$result['msg']= $res;
           // }else{
                $position=\Yii::$app->request->post('position');//广告位置
                $client=\Yii::$app->request->post('client');//客户端
                $version=\Yii::$app->request->post('version');//版本号

                if(empty($position)){
                    $result['msg']='没有传入广告位置';
                    return $result;
                }

                if($version=='1.1.2' && $client==2){
                    $models=Advert::find()->where(['position'=>$position])->andWhere(['checked'=>1])->orderBy('sort ASC')->limit(4)->all();
                    if($models){
                        foreach ($models as $model){
                            $result['data'][]=['id'=>$model->id,'title'=>$model->title,'position'=>$model->position ,'sort'=>$model->sort,'image'=>HTTP_PATH.$model->image,'url'=>$model->url,'client'=>$model->client,'version'=>$model->version];
                        }
                        $result['code']=200;
                        $result['msg']='获取广告图成功';
                    }else{
                        $result['msg']='没有广告图';
                    }
                }else{

                    $models=Advert::find()->where(['position'=>$position])->andWhere(['<>','checked',1])->orderBy('sort ASC')->limit(4)->all();
                    if($models){
                        foreach ($models as $model){
                            $result['data'][]=['id'=>$model->id,'title'=>$model->title,'position'=>$model->position ,'sort'=>$model->sort,'image'=>HTTP_PATH.$model->image,'url'=>$model->url,'client'=>$model->client,'version'=>$model->version];
                        }
                        $result['code']=200;
                        $result['msg']='获取广告图成功';
                    }else{
                        $result['msg']='没有广告图';
                    }

                }
           //}

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
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
          // if($res){
             // $result['msg']= $res;
          // }else{

               $user_id=\Yii::$app->request->post('user_id');
              /* if(empty($user_id)){
                   $result['msg']='未传入指定参数';
                   return $result;
               }*/

            //今日必读
            $models1=Book::find()->where(['groom'=>1])->orderBy('groom_time DESC')->limit(5)->all();
            if($models1){
                foreach ($models1 as $model1){
                    //判断是否api图书,不是拼接图片域名
                    $ImgUrl=$model1->image;
                    if($model1->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data']['read-today'][]=['book_id'=>$model1->id,'name'=>$model1->name,
                        'category'=>$model1->category->name,'author'=>$model1->author->name,
                        'view'=>$model1->clicks,'image'=>$ImgUrl,'size'=>$model1->size,
                        'score'=>$model1->score,'intro'=>$model1->intro,'is_end'=>$model1->is_end,
                        'download'=>$model1->downloads,'collection'=>$model1->collection,'author_id'=>$model1->author_id,
                        'category_id'=>$model1->category_id,'no_free'=>$model1->no,'type'=>$model1->type,
                        'create_time'=>$model1->create_time,'update_time'=>$model1->update_time,'from'=>$model1->from,
                        'is_free'=>$model1->is_free,'price'=>$model1->price,'search'=>$model1->search,'sale'=>$model1->search,
                        'ascription_name'=>$model1->information->name,'ascription_id'=>$model1->ascription,
                        'copyright_book_id'=>$model1->copyright_book_id,'last_update_chapter_id'=>$model1->last_update_chapter_id,
                        'last_update_chapter_name'=>$model1->last_update_chapter_name];
                }
            }else{
                $result['data']['read-today'][]='';
            }



            //限时秒杀
            $models2=Seckill::find()->orderBy('create_time DESC')->limit(4)->all();
            if($models2){
                foreach ($models2 as $model2){
                    //判断是否版权图书,不是拼接图片域名
                    $ImgUrl=$model2->book->image;
                    if($model2->book->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $categoty=Category::findOne(['id'=>$model2->book->category_id]);
                    $author=Author::findOne(['id'=>$model2->book->author_id]);
                    $result['data']['seckill'][]=['book_id'=>$model2->book->id,'name'=>$model2->book->name,
                        'category'=> $categoty->name,'author'=>$author->name,
                        'view'=>$model2->book->clicks,'image'=>$ImgUrl,'size'=>$model2->book->size,
                        'score'=>$model2->book->score,'intro'=>$model2->book->intro,'is_end'=>$model2->book->is_end,
                        'download'=>$model2->book->downloads,'collection'=>$model2->book->collection,'begin_time'=>$model2->begin_time,
                        'end_time'=>$model2->end_time,'people'=>$model2->people,'author_id'=>$model2->book->author_id,
                        'category_id'=>$model2->book->category_id,'no_free'=>$model2->book->no,'type'=>$model2->book->type,
                        'create_time'=>$model2->book->create_time,'update_time'=>$model2->book->update_time,'from'=>$model2->book->from,
                        'is_free'=>$model2->book->is_free,'price'=>$model2->book->price,'search'=>$model2->book->search,'sale'=>$model2->book->search,
                        'ascription_name'=>$model2->book->information->name,'ascription_id'=>$model2->book->ascription,
                        'copyright_book_id'=>$model2->book->copyright_book_id,'last_update_chapter_id'=>$model2->book->last_update_chapter_id,
                        'last_update_chapter_name'=>$model2->book->last_update_chapter_name];
                }
            }else{
                $result['data']['seckill'][]='';
            }



            //猜你喜欢
            //根据用户id查找喜欢的类型
                //是注册用户
                $userdetail=UserDetails::findOne(['user_id'=>$user_id]);
                if($userdetail){

                    if($userdetail->f_type){
                        //有自己喜欢的类型
                        $category_ids=explode('|',$userdetail->f_type);
                        //去除数组中空元素
                        $category_ids=array_filter($category_ids);
                        $index=array_rand($category_ids);
                        //遍历查询书
                        // $books=Book::find()->where(['category_id'=>$category_ids[$index]])->orderBy('score DESC')->limit(3)->all();
                        $books=Book::findBySql("SELECT * FROM book WHERE category_id=$category_ids[$index] and id >= ((SELECT MAX(id) FROM book)-(SELECT MIN(id) FROM book)) * RAND() + (SELECT MIN(id) FROM book)  LIMIT 3")->all();
                        foreach ($books as $book){
                            //判断是否版权图书,不是拼接图片域名
                            $ImgUrl= $book->image;
                            if( $book->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data']['like'][]=['book_id'=>$book->id,'name'=>$book->name,
                                'category'=>$book->category->name,'author'=>$book->author->name,
                                'view'=>$book->clicks,'image'=>$ImgUrl,'size'=>$book->size,
                                'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                                'download'=>$book->downloads,'collection'=>$book->collection,'author_id'=>$book->author_id,
                                'category_id'=>$book->category_id,'no_free'=>$book->no,'type'=>$book->type,
                                'create_time'=>$book->create_time,'update_time'=>$book->update_time,'from'=>$book->from,
                                'is_free'=>$book->is_free,'price'=>$book->price,'search'=>$book->search,'sale'=>$book->search,
                                'ascription_name'=>$book->information->name,'ascription_id'=>$book->ascription,
                                'copyright_book_id'=>$book->copyright_book_id,'last_update_chapter_id'=>$book->last_update_chapter_id,
                                'last_update_chapter_name'=>$book->last_update_chapter_name];
                        }
                        $result['code']=200;
                        $result['msg']='获取书城信息成功';
                        return  $result;
                    }else{
                        $books=Book::findBySql("SELECT * FROM book WHERE id >= ((SELECT MAX(id) FROM book)-(SELECT MIN(id) FROM book)) * RAND() + (SELECT MIN(id) FROM book)  LIMIT 3")->all();
                        foreach ($books as $book){
                            //判断是否版权图书,不是拼接图片域名
                            $ImgUrl= $book->image;
                            if( $book->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data']['like'][]=['book_id'=>$book->id,'name'=>$book->name,
                                'category'=>$book->category->name,'author'=>$book->author->name,
                                'view'=>$book->clicks,'image'=>$ImgUrl,'size'=>$book->size,
                                'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                                'download'=>$book->downloads,'collection'=>$book->collection,'author_id'=>$book->author_id,
                                'category_id'=>$book->category_id,'no_free'=>$book->no,'type'=>$book->type,
                                'create_time'=>$book->create_time,'update_time'=>$book->update_time,'from'=>$book->from,
                                'is_free'=>$book->is_free,'price'=>$book->price,'search'=>$book->search,'sale'=>$book->search,
                                'ascription_name'=>$book->information->name,'ascription_id'=>$book->ascription,
                                'copyright_book_id'=>$book->copyright_book_id,'last_update_chapter_id'=>$book->last_update_chapter_id,
                                'last_update_chapter_name'=>$book->last_update_chapter_name];
                        }
                        $result['code']=200;
                        $result['msg']='获取书城信息成功';

                    }

                }else{
                    //没有注册以及没有喜欢的类型
                    $books=Book::findBySql("SELECT * FROM book WHERE id >= ((SELECT MAX(id) FROM book)-(SELECT MIN(id) FROM book)) * RAND() + (SELECT MIN(id) FROM book)  LIMIT 3")->all();
                    foreach ($books as $book){
                        //判断是否版权图书,不是拼接图片域名
                        $ImgUrl= $book->image;
                        if( $book->is_api==0){
                            $ImgUrl=HTTP_PATH.$ImgUrl;
                        }
                        $result['data']['like'][]=['book_id'=>$book->id,'name'=>$book->name,
                            'category'=>$book->category->name,'author'=>$book->author->name,
                            'view'=>$book->clicks,'image'=>$ImgUrl,'size'=>$book->size,
                            'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                            'download'=>$book->downloads,'collection'=>$book->collection,'author_id'=>$book->author_id,
                            'category_id'=>$book->category_id,'no_free'=>$book->no,'type'=>$book->type,
                            'create_time'=>$book->create_time,'update_time'=>$book->update_time,'from'=>$book->from,
                            'is_free'=>$book->is_free,'price'=>$book->price,'search'=>$book->search,'sale'=>$book->search,
                            'ascription_name'=>$book->information->name,'ascription_id'=>$book->ascription,
                            'copyright_book_id'=>$book->copyright_book_id,'last_update_chapter_id'=>$book->last_update_chapter_id,
                            'last_update_chapter_name'=>$book->last_update_chapter_name];
                    }
                    $result['code']=200;
                    $result['msg']='获取书城信息成功';
                }



            /*$ids=[];
            $categorys=Category::findBySql("select id from category")->all();
            foreach ($categorys as $category){
                $ids[$category->id]=$category->id;
            }
            $index2=array_rand($ids);
            $books=Book::find()->where(['category_id'=>$index2])->orderBy('score DESC')->limit(3)->all();*/

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
                    $image=\Yii::$app->db->createCommand("select image from book WHERE category_id=$category->id AND `is_api`=0 ORDER BY create_time DESC ")->queryScalar();
                    $count1=Book::find()->andWhere(['category_id'=>$category->id])->count('id');//查询分类书的数量
                    $result['data']['male'][]=['name'=>$category->name,'intro'=>$category->intro,
                        'status'=>$category->status,'count'=>$category->count,'type'=>$category->type,
                    'image'=>HTTP_PATH.$image,'category_id'=>$category->id,'count'=>$count1];
                }
                //查询女频
            $categorys2=Category::find()->where(['type'=>0])->all();
            foreach ($categorys2 as $category){
                $image=\Yii::$app->db->createCommand("select image from book WHERE category_id=$category->id AND `is_api`=0 ORDER BY create_time DESC ")->queryScalar();
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
        ];
        if(\Yii::$app->request->isGet){
            //$obj=new Verification();
            //$res=$obj->check();
            //if($res){
              // $result['msg']= $res;
           // }else{
                $category_id=\Yii::$app->request->get('category_id');
                $page=\Yii::$app->request->get('page');
                $type=\Yii::$app->request->get('type');
                $query=Book::find()->where(['category_id'=>$category_id]);
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
                if($model->is_api==0){
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
            $result['msg']='获取分类二级页面成功';
           // }

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
                //$books1=Book::find()->where(['category_id'=>$category_id])->orderBy('score DESC')->limit(4)->all();
                $books1=Book::findBySql("SELECT * FROM `book` 
WHERE id >= (SELECT floor( RAND() * ((SELECT MAX(id) FROM `book` WHERE category_id=$category_id)-(SELECT MIN(id) FROM `book` WHERE category_id=$category_id)) + (SELECT MIN(id) FROM `book` WHERE category_id=$category_id)))  AND category_id=$category_id
ORDER BY id LIMIT 3")->all();
                foreach ($books1 as $book1){
                    //判断是否版权图书,不是拼接图片域名
                    $ImgUrl=$book1->image;
                    if($book1->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data']['similar'][]=['book_id'=>$book1->id,'name'=>$book1->name,
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
                //查找作者图书推荐
                $books2=Book::find()->where(['author_id'=>$author_id])->orderBy('score DESC')->limit(4)->all();
                foreach ($books2 as $book2){
                    //判断是否版权图书,不是拼接图片域名
                    $ImgUrl=$book2->image;
                    if($book2->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data']['author'][]=['book_id'=>$book2->id,'name'=>$book2->name,
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
        ];
        if(\Yii::$app->request->post()){
            $obj=new Verification();
            $res=$obj->check();
           if($res){
              $result['msg']= $res;
            }else{
                $book_id=\Yii::$app->request->post('book_id');
                $book=Book::findOne(['id'=>$book_id]);
                //根据图书归属查询最后一个章节
               if($book->ascription==1){
                   //请求地址
                   $postUrl = 'http://partner.chuangbie.com/partner/chapterlist';
                   $curlPost = [
                       'partner_id' => 2130,
                       'partner_sign' => 'b42c36ddd1a5cc2c6895744143f77b7b',
                       'book_id' => $book->copyright_book_id,
                   ];

                   $post = new PostRequest();
                   $data = $post->request_post($postUrl, $curlPost);
                   $data = json_decode($data, true);
                   $last_chapter=$data['content']['data'][count($data['content']['data'])-1];
                   $last_chapter_name=$last_chapter['chapter_name'];
               }elseif($book->ascription==4){
                   $get=new PostRequest();
                   $data=$get->send_request('http://api.17k.com/v2/book/'.$book->copyright_book_id.'/volumes',

                       [
                           '_access_version'=>2,
                           '_versions'=>958,
                           'access_token'=>'',
                           'app_key'=>2222420362,
                       ]
                   );
                   $datas=(json_decode($data));
                   foreach ($datas->data->volumes as $rows){
                       $last_chapter=$rows->chapters[count($rows->chapters)-1];
                       $last_chapter_name=$last_chapter->name;
                   }

               }elseif($book->ascription==5){
                   $table=Chapter::resetPartitionIndex($book_id);
                   if($table!=0){
                       $chapter=Chapter::find()->select('chapter_name')->where(['book_id'=>$book_id])->orderBy('no ASC')->column();
                       $last_chapter_name=$chapter[count($chapter)-1];
                   }else{
                       $last_chapter_name='查看目录';

                   }
               }
               //判断是否版权图书,不是拼接图片域名
               $ImgUrl=$book->image;
               if($book->is_api==0){
                   $ImgUrl=HTTP_PATH.$ImgUrl;
               }
                    $result['data']=['book_id'=>$book->id,'name'=>$book->name,
                        'category'=>$book->category->name,'author'=>$book->author->name,
                        'view'=>$book->clicks,'image'=>$ImgUrl,'size'=>$book->size,
                        'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                        'download'=>$book->downloads,'collection'=>$book->collection,'author_id'=>$book->author_id,
                        'category_id'=>$book->category_id,'no_free'=>$book->no,'type'=>$book->type,
                        'create_time'=>$book->create_time,'update_time'=>$book->update_time,'from'=>$book->from,
                        'is_free'=>$book->is_free,'price'=>$book->price,'search'=>$book->search,'sale'=>$book->search,
                        'ascription_name'=>$book->information->name,'ascription_id'=>$book->ascription,
                        'copyright_book_id'=>$book->copyright_book_id,'last_update_chapter_id'=>$book->last_update_chapter_id,
                        'last_update_chapter_name'=>$book->last_update_chapter_name];
                    $result['code']=200;
                    $result['last_chapter_name']=$last_chapter_name;
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
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
           // if($res){
              //  $result['msg']= $res;
               // return $result;
         //}else{
                $keyword=\Yii::$app->request->post('keyword');
                $keyword=trim($keyword);
                //var_dump($keyword);exit;
                if(!$keyword){
                    $result['msg']='请输入搜索关键字';
                    return $result;
                }
                //查询是否存在该关键字热词
                $word=Word::findOne(['name'=>$keyword]);
                if($word){
                    //数据库有改热词
                    $word->count=$word->count+1;
                    $word->save();
                }else{
                    //数据库无该热词
                    $model=new Word();
                    $model->name=$keyword;
                    $model->create_time=time();
                    $model->count=1;
                    $model->save();
                }
                //$authors=Author::find()->where(['like','name',$keyword])->limit(50)->all();
                $authors=Author::findBySql("select * ,(length(name)-length('$keyword')) as rn from author where name like '%$keyword%' order by rn limit 50")->all();
                //$books=Book::find()->where(['like','name',$keyword])->limit(50)->all();
                $books=Book::findBySql("select * ,(length(name)-length('$keyword')) as rn from book where name like '%$keyword%' order by rn limit 50")->all();
                if(!$books && !$authors){
                    $result['msg']='未搜索到结果';
                    return $result;
                }
                if($authors){

                    foreach ($authors as $author){
                        $count=Book::find()->andWhere(['author_id'=>$author->id])->count('id');
                        $category_id=\Yii::$app->db->createCommand("SELECT category_id, count(*) AS count FROM book WHERE author_id=$author->id GROUP BY category_id ORDER BY count DESC")->queryScalar();
                        $good_type=Category::find()->select('name')->where(['id'=>$category_id])->scalar();



                        $result['data']['author'][]=['author_id'=>$author->id,'author_name'=>$author->name,'author_image'=>HTTP_PATH.$author->image,
                            'author_intro'=>$author->intro,'popularity'=>$author->popularity,'sign'=>$author->sign,'count'=>$count,'good_type'=>$good_type];
                    }

                }

                if($books){
                    foreach ($books as $book){
               //判断是否版权图书,不拼接图片域名
               $ImgUrl=$book->image;
               if($book->is_api==0){
                   $ImgUrl=HTTP_PATH.$ImgUrl;
               }
                        $result['data']['book'][]=['book_id'=>$book->id,'name'=>$book->name,
                            'category'=>$book->category->name,'author'=>$book->author->name,
                            'view'=>$book->clicks,'image'=>$ImgUrl,'size'=>$book->size,
                            'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                            'download'=>$book->downloads,'collection'=>$book->collection,'author_id'=>$book->author_id,
                            'category_id'=>$book->category_id,'no_free'=>$book->no,'type'=>$book->type,
                            'create_time'=>$book->create_time,'update_time'=>$book->update_time,'from'=>$book->from,
                            'is_free'=>$book->is_free,'price'=>$book->price,'search'=>$book->search,'sale'=>$book->search,
                            'ascription_name'=>$book->information->name,'ascription_id'=>$book->ascription,
                            'copyright_book_id'=>$book->copyright_book_id,'last_update_chapter_id'=>$book->last_update_chapter_id,
                            'last_update_chapter_name'=>$book->last_update_chapter_name];

                        //图书搜索数+1
                        $book->search=$book->search+1;
                        $book->save();
                    }

                }
                    $result['code']=200;
                    $result['msg']='搜索信息如下';

           //}
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //搜索热词
    public function actionHotWord(){
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
                $words=Word::find()->orderBy('count DESC')->limit(6)->all();
                foreach ($words as $word){
                    $result['data'][]=$word->name;
                }
                $result['code']=200;
                $result['msg']='获取热词成功';
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
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
         if($res){
               $result['msg']= $res;
           }else{
               //根据时间排序,取出推荐分类前5
                $categorys=Category::find()->orderBy('groom_time DESC')->limit(5)->all();

                //遍历出分类id
                foreach ($categorys as $category){
                    $ids[]=$category->id;
                }
                //var_dump($ids);exit;
               //根据推荐时间取出分类推荐
                $models1=Book::find()->where(['category_id'=>$ids[0]])->andWhere(['groom'=>7])->orderBy('groom_time DESC')->limit(3)->all();
                foreach ($models1 as $model1){
                    //判断是否版权图书,不拼接图片域名
                    $ImgUrl=$model1->image;
                    if($model1->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data']['category1'][]=['book_id'=>$model1->id,'name'=>$model1->name,
                        'category'=>$model1->category->name,'author'=>$model1->author->name,
                        'view'=>$model1->clicks,'image'=> $ImgUrl,'size'=>$model1->size,
                        'score'=>$model1->score,'intro'=>$model1->intro,'is_end'=>$model1->is_end,
                        'download'=>$model1->downloads,'collection'=>$model1->collection,'author_id'=>$model1->author_id,
                        'category_id'=>$model1->category_id,'no_free'=>$model1->no,'type'=>$model1->type,
                        'create_time'=>$model1->create_time,'update_time'=>$model1->update_time,'from'=>$model1->from,
                        'is_free'=>$model1->is_free,'price'=>$model1->price,'search'=>$model1->search,'sale'=>$model1->search,
                        'ascription_name'=>$model1->information->name,'ascription_id'=>$model1->ascription,
                        'copyright_book_id'=>$model1->copyright_book_id,'last_update_chapter_id'=>$model1->last_update_chapter_id,
                        'last_update_chapter_name'=>$model1->last_update_chapter_name];
                }

            $models2=Book::find()->where(['category_id'=>$ids[1]])->andWhere(['groom'=>7])->orderBy('groom_time DESC')->limit(3)->all();
            foreach ($models2 as $model2){
                //判断是否版权图书,不拼接图片域名
                $ImgUrl=$model2->image;
                if($model2->is_api==0){
                    $ImgUrl=HTTP_PATH.$ImgUrl;
                }
                $result['data']['category2'][]=['book_id'=>$model2->id,'name'=>$model2->name,
                    'category'=>$model2->category->name,'author'=>$model2->author->name,
                    'view'=>$model2->clicks,'image'=>$ImgUrl,'size'=>$model2->size,
                    'score'=>$model2->score,'intro'=>$model2->intro,'is_end'=>$model2->is_end,
                    'download'=>$model2->downloads,'collection'=>$model2->collection,'author_id'=>$model2->author_id,
                    'category_id'=>$model2->category_id,'no_free'=>$model2->no,'type'=>$model2->type,
                    'create_time'=>$model2->create_time,'update_time'=>$model2->update_time,'from'=>$model2->from,
                    'is_free'=>$model2->is_free,'price'=>$model2->price,'search'=>$model2->search,'sale'=>$model2->search,
                    'ascription_name'=>$model2->information->name,'ascription_id'=>$model2->ascription,
                    'copyright_book_id'=>$model2->copyright_book_id,'last_update_chapter_id'=>$model2->last_update_chapter_id,
                    'last_update_chapter_name'=>$model2->last_update_chapter_name];
            }

            $models3=Book::find()->where(['category_id'=>$ids[2]])->andWhere(['groom'=>7])->orderBy('groom_time DESC')->limit(3)->all();
            foreach ($models3 as $model3){
                //判断是否版权图书,不拼接图片域名
                $ImgUrl=$model3->image;
                if($model3->is_api==0){
                    $ImgUrl=HTTP_PATH.$ImgUrl;
                }
                $result['data']['category3'][]=['book_id'=>$model3->id,'name'=>$model3->name,
                    'category'=>$model3->category->name,'author'=>$model3->author->name,
                    'view'=>$model3->clicks,'image'=>$ImgUrl,'size'=>$model3->size,
                    'score'=>$model3->score,'intro'=>$model3->intro,'is_end'=>$model3->is_end,
                    'download'=>$model3->downloads,'collection'=>$model3->collection,'author_id'=>$model3->author_id,
                    'category_id'=>$model3->category_id,'no_free'=>$model3->no,'type'=>$model3->type,
                    'create_time'=>$model3->create_time,'update_time'=>$model3->update_time,'from'=>$model3->from,
                    'is_free'=>$model3->is_free,'price'=>$model3->price,'search'=>$model3->search,'sale'=>$model3->search,
                    'ascription_name'=>$model3->information->name,'ascription_id'=>$model3->ascription,
                    'copyright_book_id'=>$model3->copyright_book_id,'last_update_chapter_id'=>$model3->last_update_chapter_id,
                    'last_update_chapter_name'=>$model3->last_update_chapter_name];
            }

            $models4=Book::find()->where(['category_id'=>$ids[3]])->andWhere(['groom'=>7])->orderBy('groom_time DESC')->limit(3)->all();
            foreach ($models4 as $model4){
                //判断是否版权图书,不拼接图片域名
                $ImgUrl=$model4->image;
                if($model4->is_api==0){
                    $ImgUrl=HTTP_PATH.$ImgUrl;
                }
                $result['data']['category4'][]=['book_id'=>$model4->id,'name'=>$model4->name,
                    'category'=>$model4->category->name,'author'=>$model4->author->name,
                    'view'=>$model4->clicks,'image'=>$ImgUrl,'size'=>$model4->size,
                    'score'=>$model4->score,'intro'=>$model4->intro,'is_end'=>$model4->is_end,
                    'download'=>$model4->downloads,'collection'=>$model4->collection,'author_id'=>$model4->author_id,
                    'category_id'=>$model4->category_id,'no_free'=>$model4->no,'type'=>$model4->type,
                    'create_time'=>$model4->create_time,'update_time'=>$model4->update_time,'from'=>$model4->from,
                    'is_free'=>$model4->is_free,'price'=>$model4->price,'search'=>$model4->search,'sale'=>$model4->search,
                    'ascription_name'=>$model4->information->name,'ascription_id'=>$model4->ascription,
                    'copyright_book_id'=>$model4->copyright_book_id,'last_update_chapter_id'=>$model4->last_update_chapter_id,
                    'last_update_chapter_name'=>$model4->last_update_chapter_name];
            }

            $models5=Book::find()->where(['category_id'=>$ids[4]])->andWhere(['groom'=>7])->orderBy('groom_time DESC')->limit(3)->all();
            foreach ($models5 as $model5){
                //判断是否版权图书,不拼接图片域名
                $ImgUrl=$model5->image;
                if($model5->is_api==0){
                    $ImgUrl=HTTP_PATH.$ImgUrl;
                }
                $result['data']['category5'][]=['book_id'=>$model5->id,'name'=>$model5->name,
                    'category'=>$model5->category->name,'author'=>$model5->author->name,
                    'view'=>$model5->clicks,'image'=>$ImgUrl,'size'=>$model5->size,
                    'score'=>$model5->score,'intro'=>$model5->intro,'is_end'=>$model5->is_end,
                    'download'=>$model5->downloads,'collection'=>$model5->collection,'author_id'=>$model5->author_id,
                    'category_id'=>$model5->category_id,'no_free'=>$model5->no,'type'=>$model5->type,
                    'create_time'=>$model5->create_time,'update_time'=>$model5->update_time,'from'=>$model5->from,
                    'is_free'=>$model5->is_free,'price'=>$model5->price,'search'=>$model5->search,'sale'=>$model5->search,
                    'ascription_name'=>$model5->information->name,'ascription_id'=>$model5->ascription,
                    'copyright_book_id'=>$model5->copyright_book_id,'last_update_chapter_id'=>$model5->last_update_chapter_id,
                    'last_update_chapter_name'=>$model5->last_update_chapter_name];
            }
            $result['code']=200;
            $result['msg']='获取信息成功';
         }
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
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
           if($res){
              $result['msg']= $res;
            }else{
            //今日限免
                $books1=Book::find()->where(['groom'=>2])->orderBy('groom_time DESC')->limit(3)->all();
                foreach ( $books1 as $book1){
                    //判断是否版权图书,不拼接图片域名
                    $ImgUrl=$book1->image;
                    if($book1->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data']['today-free'][]=['book_id'=>$book1->id,'name'=>$book1->name,
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

                //女生限免
                $books2=Book::find()->where(['groom'=>3])->orderBy('groom_time DESC')->limit(8)->all();
                foreach ( $books2 as $book2){
                    //判断是否版权图书,不拼接图片域名
                    $ImgUrl=$book2->image;
                    if($book2->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data']['female-free'][]=['book_id'=>$book2->id,'name'=>$book2->name,
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

                //男生限免
                $books3=Book::find()->where(['groom'=>4])->orderBy('groom_time DESC')->limit(3)->all();
                foreach ( $books3 as $book3){
                    //判断是否版权图书,不拼接图片域名
                    $ImgUrl=$book3->image;
                    if($book3->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data']['male-free'][]=['book_id'=>$book3->id,'name'=>$book3->name,
                        'category'=>$book3->category->name,'author'=>$book3->author->name,
                        'view'=>$book3->clicks,'image'=>$ImgUrl,'size'=>$book3->size,
                        'score'=>$book3->score,'intro'=>$book3->intro,'is_end'=>$book3->is_end,
                        'download'=>$book3->downloads,'collection'=>$book3->collection,'author_id'=>$book3->author_id,
                        'category_id'=>$book3->category_id,'no_free'=>$book3->no,'type'=>$book3->type,
                        'create_time'=>$book3->create_time,'update_time'=>$book3->update_time,'from'=>$book3->from,
                        'is_free'=>$book3->is_free,'price'=>$book3->price,'search'=>$book3->search,'sale'=>$book3->search,
                        'ascription_name'=>$book3->information->name,'ascription_id'=>$book3->ascription,
                        'copyright_book_id'=>$book3->copyright_book_id,'last_update_chapter_id'=>$book3->last_update_chapter_id,
                        'last_update_chapter_name'=>$book3->last_update_chapter_name];
                }
                $result['msg']='获取书本信息成功';
                $result['code']=200;
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //书城完本限免
    public function actionEndBook(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
           // $obj=new Verification();
           // $res=$obj->check();
           // if($res){
             //$result['msg']= $res;
           // }else{
            //根据传入groom查询是什么完本
                $groom=\Yii::$app->request->post('groom');
                $type=\Yii::$app->request->post('type');
                $ManBooks1=Book::find()->where(['groom'=>$groom])->orderBy('groom_time DESC')->limit(6)->all();
                foreach ($ManBooks1 as $Manbook1){
                    //判断是否版权图书,不拼接图片域名
                    $ImgUrl=$Manbook1->image;
                    if($Manbook1->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data']['end_book'][]=['book_id'=>$Manbook1->id,'name'=>$Manbook1->name,
                        'category'=>$Manbook1->category->name,'author'=>$Manbook1->author->name,
                        'view'=>$Manbook1->clicks,'image'=>$ImgUrl,'size'=>$Manbook1->size,
                        'score'=>$Manbook1->score,'intro'=>$Manbook1->intro,'is_end'=>$Manbook1->is_end,
                        'download'=>$Manbook1->downloads,'collection'=>$Manbook1->collection,'author_id'=>$Manbook1->author_id,
                        'category_id'=>$Manbook1->category_id,'no_free'=>$Manbook1->no,'type'=>$Manbook1->type,
                        'create_time'=>$Manbook1->create_time,'update_time'=>$Manbook1->update_time,'from'=>$Manbook1->from,
                        'is_free'=>$Manbook1->is_free,'price'=>$Manbook1->price,'search'=>$Manbook1->search,'sale'=>$Manbook1->search,
                        'ascription_name'=>$Manbook1->information->name,'ascription_id'=>$Manbook1->ascription,
                        'copyright_book_id'=>$Manbook1->copyright_book_id,'last_update_chapter_id'=>$Manbook1->last_update_chapter_id,
                        'last_update_chapter_name'=>$Manbook1->last_update_chapter_name];
                }

                //根据传入type判断是男生还是女生,随机查询男生和女生下面的分类
                $ManIds=\Yii::$app->db->createCommand("SELECT id FROM category WHERE type=$type")->queryColumn();
                $ManBooks2=Book::find()->where(['category_id'=>$ManIds[array_rand($ManIds,1)],'is_end'=>1])->orderBy('score DESC')->limit(3)->all();
                foreach ($ManBooks2 as $Manbook2){
                    //判断是否版权图书,不拼接图片域名
                    $ImgUrl=$Manbook2->image;
                    if($Manbook2->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data']['end_category'][]=['book_id'=>$Manbook2->id,'name'=>$Manbook2->name,
                        'category'=>$Manbook2->category->name,'author'=>$Manbook2->author->name,
                        'view'=>$Manbook2->clicks,'image'=>$ImgUrl,'size'=>$Manbook2->size,
                        'score'=>$Manbook2->score,'intro'=>$Manbook2->intro,'is_end'=>$Manbook2->is_end,
                        'download'=>$Manbook2->downloads,'collection'=>$Manbook2->collection,'author_id'=>$Manbook2->author_id,
                        'category_id'=>$Manbook2->category_id,'no_free'=>$Manbook2->no,'type'=>$Manbook2->type,
                        'create_time'=>$Manbook2->create_time,'update_time'=>$Manbook2->update_time,'from'=>$Manbook2->from,
                        'is_free'=>$Manbook2->is_free,'price'=>$Manbook2->price,'search'=>$Manbook2->search,'sale'=>$Manbook2->search,
                        'ascription_name'=>$Manbook2->information->name,'ascription_id'=>$Manbook2->ascription,
                        'copyright_book_id'=>$Manbook2->copyright_book_id,'last_update_chapter_id'=>$Manbook2->last_update_chapter_id,
                        'last_update_chapter_name'=>$Manbook2->last_update_chapter_name];
                }
                $result['code']=200;
                $result['msg']='获取书本信息成功';
           // }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //根据作者id查询书
    public function actionAuthorBook(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
           // $obj=new Verification();
           // $res=$obj->check();
           // if($res){
           //  $result['msg']= $res;
           // }else{
                $author_id=\Yii::$app->request->post('author_id');
                $books=Book::find()->where(['author_id'=>$author_id])->orderBy('score DESC')->all();
                foreach ($books as $book){
                    //判断是否版权图书,不拼接图片域名
                    $ImgUrl=$book->image;
                    if($book->is_api==0){
                        $ImgUrl=HTTP_PATH.$ImgUrl;
                    }
                    $result['data'][]=['book_id'=>$book->id,'name'=>$book->name,
                        'category'=>$book->category->name,'author'=>$book->author->name,
                        'view'=>$book->clicks,'image'=> $ImgUrl,'size'=>$book->size,
                        'score'=>$book->score,'intro'=>$book->intro,'is_end'=>$book->is_end,
                        'download'=>$book->downloads,'collection'=>$book->collection,'author_id'=>$book->author_id,
                        'category_id'=>$book->category_id,'no_free'=>$book->no,'type'=>$book->type,
                        'create_time'=>$book->create_time,'update_time'=>$book->update_time,'from'=>$book->from,
                        'is_free'=>$book->is_free,'price'=>$book->price,'search'=>$book->search,'sale'=>$book->search,
                        'ascription_name'=>$book->information->name,'ascription_id'=>$book->ascription,
                        'copyright_book_id'=>$book->copyright_book_id,'last_update_chapter_id'=>$book->last_update_chapter_id,
                        'last_update_chapter_name'=>$book->last_update_chapter_name];
                }
                $result['code']=200;
                $result['msg']='获取作者书籍成功';
          //  }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //精品更多
    public function actionBoutiqueMore(){
        $result=[
          'code'=>400,
          'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isGet){
            $obj=new Verification();
             $res=$obj->check();
             //验证接口
             if($res){
                $result['msg']= $res;
             }else{
                 //接收参数
                 $category_id=\Yii::$app->request->get('category_id');
                 $page=\Yii::$app->request->get('page');
                 if(empty($category_id)){
                     $result['msg']='未传入指定参数';
                     return $result;
                 }
                 $query=Book::find()->where(['category_id'=>$category_id]);
                 $count=ceil($query->count()/5);
                 if($page>$count){
                     $result['msg']='没有更多了';
                     return $result;
                 }
                 $pager=new Pagination([
                     'totalCount'=>$query->count(),
                     'defaultPageSize'=>5,
                 ]);
                 $models=$query->limit($pager->limit)->offset($pager->offset)->all();
                 if($models){
                     foreach ($models as $model){
                         //判断是否版权图书,不是拼接图片域名
                         $ImgUrl=$model->image;
                         if($model->is_api==0){
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
                     $result['msg']='成功获取图书信息';
                 }else{
                     $result['code']=404;
                     $result['msg']='没有图书';
                 }
             }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //免费更多
    public function actionFreeMore(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isGet){
            $obj=new Verification();
            $res=$obj->check();
            //验证接口
            if($res){
                $result['msg']= $res;
            }else{
                //接口验证通过
                //接收参数
                $type=\Yii::$app->request->get('type');//1是今日免费;2女生免费;3男生免费
                $page=\Yii::$app->request->get('page');
                if(empty($type)){
                    $result['msg']='未传入指定参数';
                    return $result;
                }
                if($type==1){
                    $query=Book::find()->where(['is_free'=>0]);
                }elseif($type==2){
                    //查询属于女生的分类id
                    $ManIds=\Yii::$app->db->createCommand("SELECT id FROM category WHERE type=0")->queryColumn();
                    $query=Book::find()->where(['category_id'=>$ManIds])->andWhere(['is_free'=>0]);
                }elseif($type==3){
                    $ManIds=\Yii::$app->db->createCommand("SELECT id FROM category WHERE type=1")->queryColumn();
                    $query=Book::find()->where(['category_id'=>$ManIds])->andWhere(['is_free'=>0]);
                }

                $count=ceil($query->count()/5);
                if($page>$count){
                    $result['msg']='没有更多了';
                    return $result;
                }
                $pager=new Pagination([
                    'totalCount'=>$query->count(),
                    'defaultPageSize'=>5,
                ]);
                $models=$query->limit($pager->limit)->offset($pager->offset)->all();
                if($models){
                    foreach ($models as $model){
                        //判断是否版权图书,不是拼接图片域名
                        $ImgUrl=$model->image;
                        if($model->is_api==0){
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
                    $result['msg']='成功获取图书信息';
                }else{
                    $result['code']=404;
                    $result['msg']='没有图书';
                }
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //完结更多
    public function actionOverMore(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isGet){
            $obj=new Verification();
            $res=$obj->check();
            //验证接口
            //if($res){
               // $result['msg']= $res;
            //}else{
                //接口验证通过
                //接收参数
                $category_id=\Yii::$app->request->get('category_id');
                $type=\Yii::$app->request->get('type');
                $page=\Yii::$app->request->get('page');
                if(empty($type)){
                    $result['msg']='未传入指定参数';
                    return $result;
                }
                if($type==1){
                    $ManIds=\Yii::$app->db->createCommand("SELECT id FROM category WHERE type=1")->queryColumn();
                    if(empty($category_id)){
                        $query=Book::find()->where(['is_end'=>2])->andWhere(['category_id'=>$ManIds]);
                    }else{
                        $query=Book::find()->where(['is_end'=>2])->andWhere(['category_id'=>$category_id]);
                    }
                    $count=ceil($query->count()/5);
                    if($page>$count){
                        $result['msg']='没有更多了';
                        return $result;
                    }
                    $pager=new Pagination([
                        'totalCount'=>$query->count(),
                        'defaultPageSize'=>5,
                    ]);
                    $models=$query->limit($pager->limit)->offset($pager->offset)->all();
                    if($models){
                        foreach ($models as $model){
                            //判断是否版权图书,不是拼接图片域名
                            $ImgUrl=$model->image;
                            if($model->is_api==0){
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
                        $result['msg']='成功获取图书信息';
                    }else{
                        $result['code']=404;
                        $result['msg']='没有图书';
                    }

                }elseif($type==2){

                    $ManIds=\Yii::$app->db->createCommand("SELECT id FROM category WHERE type=0")->queryColumn();
                    if(empty($category_id)){
                        $query=Book::find()->where(['is_end'=>2])->andWhere(['category_id'=>$ManIds]);
                    }else{
                        $query=Book::find()->where(['is_end'=>2])->andWhere(['category_id'=>$category_id]);
                    }
                    $count=ceil($query->count()/5);
                    if($page>$count){
                        $result['msg']='没有更多了';
                        return $result;
                    }
                    $pager=new Pagination([
                        'totalCount'=>$query->count(),
                        'defaultPageSize'=>5,
                    ]);
                    $models=$query->limit($pager->limit)->offset($pager->offset)->all();
                    if($models){
                        foreach ($models as $model){
                            //判断是否版权图书,不是拼接图片域名
                            $ImgUrl=$model->image;
                            if($model->is_api==0){
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
                        $result['msg']='成功获取图书信息';
                    }else{
                        $result['code']=404;
                        $result['msg']='没有图书';
                    }
                }


          // }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}