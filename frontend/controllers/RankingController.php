<?php
namespace frontend\controllers;
use backend\models\Book;
use backend\models\Category;
use frontend\models\Word;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

class RankingController extends Controller{
    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //书城排行
    public function actionIndex(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>[],
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            //$res=$obj->check();
           // if($res){
                //$result['msg']= $res;
           // }else{
                $sex=\Yii::$app->request->post('sex');
                $type=\Yii::$app->request->post('type');
                if($sex==1){
                    //查询属于男生的分类id
                    $ManIds=\Yii::$app->db->createCommand("SELECT id FROM category WHERE type=1")->queryColumn();
                    if($type==1){
                        //男生畅销
                        //查询消费记录最多的

                        $SellingBooks=Book::find()->where(['groom'=>8])->orderBy('groom_time DESC')->limit(10)->all();
                        foreach ( $SellingBooks as $sellingBook) {
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$sellingBook->image;
                            if($sellingBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][] = ['book_id' => $sellingBook->id, 'name' => $sellingBook->name,
                                'category' => $sellingBook->category->name, 'author' => $sellingBook->author->name,
                                'view' => $sellingBook->clicks, 'image' => $ImgUrl, 'size' => $sellingBook->size,
                                'score' => $sellingBook->score, 'intro' => $sellingBook->intro, 'is_end' => $sellingBook->is_end,
                                'download' => $sellingBook->downloads, 'collection' => $sellingBook->collection, 'author_id' => $sellingBook->author_id,
                                'category_id' => $sellingBook->category_id, 'no_free' => $sellingBook->no, 'type' => $sellingBook->type,
                                'create_time' => $sellingBook->create_time, 'update_time' => $sellingBook->update_time,'from'=>$sellingBook->from,
                                'is_free'=>$sellingBook->is_free,'price'=>$sellingBook->price,'search'=>$sellingBook->search,'sale'=>$sellingBook->search,
                                'ascription_name'=>$sellingBook->information->name,'ascription_id'=>$sellingBook->ascription,
                                'copyright_book_id'=>$sellingBook->copyright_book_id,'last_update_chapter_id'=>$sellingBook->last_update_chapter_id,
                                'last_update_chapter_name'=>$sellingBook->last_update_chapter_name];
                        }



                    }elseif ($type==2){

                        //男生新书

                        $NewBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('create_time DESC')->limit(10)->all();

                        foreach (  $NewBooks as $newBook) {
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$newBook->image;
                            if($newBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][]= ['book_id' => $newBook->id, 'name' => $newBook->name,
                                'category' => $newBook->category->name, 'author' => $newBook->author->name,
                                'view' => $newBook->clicks, 'image' =>  $ImgUrl, 'size' => $newBook->size,
                                'score' => $newBook->score, 'intro' => $newBook->intro, 'is_end' =>$newBook->is_end,
                                'download' => $newBook->downloads, 'collection' => $newBook->collection, 'author_id' => $newBook->author_id,
                                'category_id' => $newBook->category_id, 'no_free' => $newBook->no, 'type' => $newBook->type,
                                'create_time' => $newBook->create_time, 'update_time' => $newBook->update_time,'from'=>$newBook->from,
                                'is_free'=>$newBook->is_free,'price'=>$newBook->price,'search'=>$newBook->search,'sale'=>$newBook->search,
                                'ascription_name'=>$newBook->information->name,'ascription_id'=>$newBook->ascription,
                                'copyright_book_id'=>$newBook->copyright_book_id,'last_update_chapter_id'=>$newBook->last_update_chapter_id,
                                'last_update_chapter_name'=>$newBook->last_update_chapter_name];
                        }

                    }elseif ($type==3){

                        //男生热更

                        $UpdateBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('update_time DESC')->limit(10)->all();

                        foreach ( $UpdateBooks as $updateBook) {
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$updateBook->image;
                            if($updateBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][] = ['book_id' => $updateBook->id, 'name' => $updateBook->name,
                                'category' => $updateBook->category->name, 'author' => $updateBook->author->name,
                                'view' => $updateBook->clicks, 'image' => $ImgUrl, 'size' => $updateBook->size,
                                'score' => $updateBook->score, 'intro' => $updateBook->intro, 'is_end' =>$updateBook->is_end,
                                'download' => $updateBook->downloads, 'collection' => $updateBook->collection, 'author_id' => $updateBook->author_id,
                                'category_id' => $updateBook->category_id, 'no_free' => $updateBook->no, 'type' => $updateBook->type,
                                'create_time' => $updateBook->create_time, 'update_time' => $updateBook->update_time,'from'=>$updateBook->from,
                                'is_free'=>$updateBook->is_free,'price'=>$updateBook->price,'search'=>$updateBook->search,'sale'=>$updateBook->search,
                                'ascription_name'=>$updateBook->information->name,'ascription_id'=>$updateBook->ascription,
                                'copyright_book_id'=>$updateBook->copyright_book_id,'last_update_chapter_id'=>$updateBook->last_update_chapter_id,
                                'last_update_chapter_name'=>$updateBook->last_update_chapter_name];
                        }


                    }elseif ($type==4){

                        //男生完结

                        $EndBooks=Book::find()->where(['category_id'=> $ManIds,'is_end'=>1])->orderBy('score DESC')->limit(10)->all();

                        foreach ($EndBooks as $endBook) {
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$endBook->image;
                            if($endBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][] = ['book_id' => $endBook->id, 'name' => $endBook->name,
                                'category' => $endBook->category->name, 'author' => $endBook->author->name,
                                'view' => $endBook->clicks, 'image' => $ImgUrl, 'size' => $endBook->size,
                                'score' => $endBook->score, 'intro' => $endBook->intro, 'is_end' =>$endBook->is_end,
                                'download' => $endBook->downloads, 'collection' => $endBook->collection, 'author_id' => $endBook->author_id,
                                'category_id' => $endBook->category_id, 'no_free' => $endBook->no, 'type' => $endBook->type,
                                'create_time' => $endBook->create_time, 'update_time' => $endBook->update_time,'from'=>$endBook->from,
                                'is_free'=>$endBook->is_free,'price'=>$endBook->price,'search'=>$endBook->search,'sale'=>$endBook->search,
                                'ascription_name'=>$endBook->information->name,'ascription_id'=>$endBook->ascription,
                                'copyright_book_id'=>$endBook->copyright_book_id,'last_update_chapter_id'=>$endBook->last_update_chapter_id,
                                'last_update_chapter_name'=>$endBook->last_update_chapter_name];
                        }

                    }elseif ($type==5){

                        //男生热搜
                        $HotsearchBooks=Book::find()->where(['groom'=>10])->orderBy('groom_time DESC')->limit(10)->all();


                        foreach ($HotsearchBooks as $hotsearchBook){
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$hotsearchBook->image;
                            if($hotsearchBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][] = ['book_id' => $hotsearchBook->id, 'name' => $hotsearchBook->name,
                                'category' => $hotsearchBook->category->name, 'author' => $hotsearchBook->author->name,
                                'view' => $hotsearchBook->clicks, 'image' => $ImgUrl, 'size' => $hotsearchBook->size,
                                'score' => $hotsearchBook->score, 'intro' => $hotsearchBook->intro, 'is_end' =>$hotsearchBook->is_end,
                                'download' => $hotsearchBook->downloads, 'collection' => $hotsearchBook->collection, 'author_id' => $hotsearchBook->author_id,
                                'category_id' => $hotsearchBook->category_id, 'no_free' => $hotsearchBook->no, 'type' => $hotsearchBook->type,
                                'create_time' => $hotsearchBook->create_time, 'update_time' => $hotsearchBook->update_time,'from'=>$hotsearchBook->from,
                                'is_free'=>$hotsearchBook->is_free,'price'=>$hotsearchBook->price,'search'=>$hotsearchBook->search,'sale'=>$hotsearchBook->search,
                                'ascription_name'=>$hotsearchBook->information->name,'ascription_id'=>$hotsearchBook->ascription,
                                'copyright_book_id'=>$hotsearchBook->copyright_book_id,'last_update_chapter_id'=>$hotsearchBook->last_update_chapter_id,
                                'last_update_chapter_name'=>$hotsearchBook->last_update_chapter_name];
                        }
                    }


                }elseif ($sex==2){

                    //查询属于女生的分类id
                    $ManIds=\Yii::$app->db->createCommand("SELECT id FROM category WHERE type=0")->queryColumn();
                    if($type==1){
                        //女生畅销


                        $SellingBooks=Book::find()->where(['groom'=>9])->orderBy('groom_time DESC')->limit(10)->all();
                        foreach ( $SellingBooks as $sellingBook) {
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$sellingBook->image;
                            if($sellingBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][] = ['book_id' => $sellingBook->id, 'name' => $sellingBook->name,
                                'category' => $sellingBook->category->name, 'author' => $sellingBook->author->name,
                                'view' => $sellingBook->clicks, 'image' => $ImgUrl, 'size' => $sellingBook->size,
                                'score' => $sellingBook->score, 'intro' => $sellingBook->intro, 'is_end' => $sellingBook->is_end,
                                'download' => $sellingBook->downloads, 'collection' => $sellingBook->collection, 'author_id' => $sellingBook->author_id,
                                'category_id' => $sellingBook->category_id, 'no_free' => $sellingBook->no, 'type' => $sellingBook->type,
                                'create_time' => $sellingBook->create_time, 'update_time' => $sellingBook->update_time,'from'=>$sellingBook->from,
                                'is_free'=>$sellingBook->is_free,'price'=>$sellingBook->price,'search'=>$sellingBook->search,'sale'=>$sellingBook->search,
                                'ascription_name'=>$sellingBook->information->name,'ascription_id'=>$sellingBook->ascription,
                                'copyright_book_id'=>$sellingBook->copyright_book_id,'last_update_chapter_id'=>$sellingBook->last_update_chapter_id,
                                'last_update_chapter_name'=>$sellingBook->last_update_chapter_name];
                        }



                    }elseif ($type==2){

                        //女生新书

                        $NewBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('create_time DESC')->limit(10)->all();

                        foreach (  $NewBooks as $newBook) {
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$newBook->image;
                            if($newBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][] = ['book_id' => $newBook->id, 'name' => $newBook->name,
                                'category' => $newBook->category->name, 'author' => $newBook->author->name,
                                'view' => $newBook->clicks, 'image' => $ImgUrl, 'size' => $newBook->size,
                                'score' => $newBook->score, 'intro' => $newBook->intro, 'is_end' =>$newBook->is_end,
                                'download' => $newBook->downloads, 'collection' => $newBook->collection, 'author_id' => $newBook->author_id,
                                'category_id' => $newBook->category_id, 'no_free' => $newBook->no, 'type' => $newBook->type,
                                'create_time' => $newBook->create_time, 'update_time' => $newBook->update_time,'from'=> $newBook->from,
                                'is_free'=> $newBook->is_free,'price'=> $newBook->price,'search'=> $newBook->search,'sale'=> $newBook->search,
                                'ascription_name'=> $newBook->information->name,'ascription_id'=> $newBook->ascription,
                                'copyright_book_id'=> $newBook->copyright_book_id,'last_update_chapter_id'=> $newBook->last_update_chapter_id,
                                'last_update_chapter_name'=> $newBook->last_update_chapter_name];
                        }

                    }elseif ($type==3){

                        //女生热更

                        $UpdateBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('update_time DESC')->limit(10)->all();

                        foreach ( $UpdateBooks as $updateBook) {
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$updateBook->image;
                            if($updateBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][] = ['book_id' => $updateBook->id, 'name' => $updateBook->name,
                                'category' => $updateBook->category->name, 'author' => $updateBook->author->name,
                                'view' => $updateBook->clicks, 'image' =>  $ImgUrl, 'size' => $updateBook->size,
                                'score' => $updateBook->score, 'intro' => $updateBook->intro, 'is_end' =>$updateBook->is_end,
                                'download' => $updateBook->downloads, 'collection' => $updateBook->collection, 'author_id' => $updateBook->author_id,
                                'category_id' => $updateBook->category_id, 'no_free' => $updateBook->no, 'type' => $updateBook->type,
                                'create_time' => $updateBook->create_time, 'update_time' => $updateBook->update_time,'from'=>$updateBook->from,
                                'is_free'=>$updateBook->is_free,'price'=>$updateBook->price,'search'=>$updateBook->search,'sale'=>$updateBook->search,
                                'ascription_name'=>$updateBook->information->name,'ascription_id'=>$updateBook->ascription,
                                'copyright_book_id'=>$updateBook->copyright_book_id,'last_update_chapter_id'=>$updateBook->last_update_chapter_id,
                                'last_update_chapter_name'=>$updateBook->last_update_chapter_name];
                        }


                    }elseif ($type==4){

                        //女生完结

                        $EndBooks=Book::find()->where(['category_id'=> $ManIds,'is_end'=>1])->orderBy('score DESC')->limit(10)->all();

                        foreach ($EndBooks as $endBook) {
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$endBook->image;
                            if($endBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][] =  ['book_id' => $endBook->id, 'name' => $endBook->name,
                                'category' => $endBook->category->name, 'author' => $endBook->author->name,
                                'view' => $endBook->clicks, 'image' =>  $ImgUrl, 'size' => $endBook->size,
                                'score' => $endBook->score, 'intro' => $endBook->intro, 'is_end' =>$endBook->is_end,
                                'download' => $endBook->downloads, 'collection' => $endBook->collection, 'author_id' => $endBook->author_id,
                                'category_id' => $endBook->category_id, 'no_free' => $endBook->no, 'type' => $endBook->type,
                                'create_time' => $endBook->create_time, 'update_time' => $endBook->update_time,'from'=>$endBook->from,
                                'is_free'=>$endBook->is_free,'price'=>$endBook->price,'search'=>$endBook->search,'sale'=>$endBook->search,
                                'ascription_name'=>$endBook->information->name,'ascription_id'=>$endBook->ascription,
                                'copyright_book_id'=>$endBook->copyright_book_id,'last_update_chapter_id'=>$endBook->last_update_chapter_id,
                                'last_update_chapter_name'=>$endBook->last_update_chapter_name];
                        }

                    }elseif ($type==5){

                        //女生热搜
                        $HotsearchBooks=Book::find()->where(['groom'=>5])->orderBy('groom_time DESC')->limit(10)->all();


                        foreach ($HotsearchBooks as $hotsearchBook){
                            //判断是否版权图书,不拼接图片域名
                            $ImgUrl=$hotsearchBook->image;
                            if($hotsearchBook->is_api==0){
                                $ImgUrl=HTTP_PATH.$ImgUrl;
                            }
                            $result['data'][] = ['book_id' => $hotsearchBook->id, 'name' => $hotsearchBook->name,
                                'category' => $hotsearchBook->category->name, 'author' => $hotsearchBook->author->name,
                                'view' => $hotsearchBook->clicks, 'image' => $ImgUrl, 'size' => $hotsearchBook->size,
                                'score' => $hotsearchBook->score, 'intro' => $hotsearchBook->intro, 'is_end' =>$hotsearchBook->is_end,
                                'download' => $hotsearchBook->downloads, 'collection' => $hotsearchBook->collection, 'author_id' => $hotsearchBook->author_id,
                                'category_id' => $hotsearchBook->category_id, 'no_free' => $hotsearchBook->no, 'type' => $hotsearchBook->type,
                                'create_time' => $hotsearchBook->create_time, 'update_time' => $hotsearchBook->update_time,'from'=>$hotsearchBook->from,
                                'is_free'=>$hotsearchBook->is_free,'price'=>$hotsearchBook->price,'search'=>$hotsearchBook->search,'sale'=>$hotsearchBook->search,
                                'ascription_name'=>$hotsearchBook->information->name,'ascription_id'=>$hotsearchBook->ascription,
                                'copyright_book_id'=>$hotsearchBook->copyright_book_id,'last_update_chapter_id'=>$hotsearchBook->last_update_chapter_id,
                                'last_update_chapter_name'=>$hotsearchBook->last_update_chapter_name];
                        }
                    }


                }else{
                    $result['msg']='参数错误';
                }
                $result['code']=200;
                $result['msg']='获取排行书籍成功';
           // }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}