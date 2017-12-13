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
            $res=$obj->check();
           // if($res){
              //  $result['msg']= $res;
           // }else{
                $sex=\Yii::$app->request->post('sex');
                $type=\Yii::$app->request->post('type');
                if($sex==1){
                    //查询属于男生的分类id
                    $ManIds=\Yii::$app->db->createCommand("SELECT id FROM category WHERE type=1")->queryColumn();
                    if($type==1){
                        //畅销

                        $SellingBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('collection DESC')->limit(10)->all();
                        foreach ( $SellingBooks as $sellingBook) {
                            $result['data'][] = ['book_id' => $sellingBook->id, 'name' => $sellingBook->name,
                                'category' => $sellingBook->category->name, 'author' => $sellingBook->author->name,
                                'view' => $sellingBook->clicks, 'image' => HTTP_PATH . $sellingBook->image, 'size' => $sellingBook->size,
                                'score' => $sellingBook->score, 'intro' => $sellingBook->intro, 'is_end' => $sellingBook->is_end,
                                'download' => $sellingBook->downloads, 'collection' => $sellingBook->collection, 'author_id' => $sellingBook->author_id,
                                'category_id' => $sellingBook->category_id, 'no_free' => $sellingBook->no, 'type' => $sellingBook->type,
                                'create_time' => $sellingBook->create_time, 'update_time' => $sellingBook->update_time];
                        }



                    }elseif ($type==2){

                        //新书

                        $NewBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('create_time DESC')->limit(10)->all();

                        foreach (  $NewBooks as $newBook) {
                            $result['data'][]= ['book_id' => $newBook->id, 'name' => $newBook->name,
                                'category' => $newBook->category->name, 'author' => $newBook->author->name,
                                'view' => $newBook->clicks, 'image' => HTTP_PATH . $newBook->image, 'size' => $newBook->size,
                                'score' => $newBook->score, 'intro' => $newBook->intro, 'is_end' =>$newBook->is_end,
                                'download' => $newBook->downloads, 'collection' => $newBook->collection, 'author_id' => $newBook->author_id,
                                'category_id' => $newBook->category_id, 'no_free' => $newBook->no, 'type' => $newBook->type,
                                'create_time' => $newBook->create_time, 'update_time' => $newBook->update_time];
                        }

                    }elseif ($type==3){

                        //热更

                        $UpdateBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('update_time DESC')->limit(10)->all();

                        foreach ( $UpdateBooks as $updateBook) {
                            $result['data'][] = ['book_id' => $updateBook->id, 'name' => $updateBook->name,
                                'category' => $updateBook->category->name, 'author' => $updateBook->author->name,
                                'view' => $updateBook->clicks, 'image' => HTTP_PATH . $updateBook->image, 'size' => $updateBook->size,
                                'score' => $updateBook->score, 'intro' => $updateBook->intro, 'is_end' =>$updateBook->is_end,
                                'download' => $updateBook->downloads, 'collection' => $updateBook->collection, 'author_id' => $updateBook->author_id,
                                'category_id' => $updateBook->category_id, 'no_free' => $updateBook->no, 'type' => $updateBook->type,
                                'create_time' => $updateBook->create_time, 'update_time' => $updateBook->update_time];
                        }


                    }elseif ($type==4){

                        //完结

                        $EndBooks=Book::find()->where(['category_id'=> $ManIds,'is_end'=>1])->orderBy('score DESC')->limit(10)->all();

                        foreach ($EndBooks as $endBook) {
                            $result['data'][] = ['book_id' => $endBook->id, 'name' => $endBook->name,
                                'category' => $endBook->category->name, 'author' => $endBook->author->name,
                                'view' => $endBook->clicks, 'image' => HTTP_PATH . $endBook->image, 'size' => $endBook->size,
                                'score' => $endBook->score, 'intro' => $endBook->intro, 'is_end' =>$endBook->is_end,
                                'download' => $endBook->downloads, 'collection' => $endBook->collection, 'author_id' => $endBook->author_id,
                                'category_id' => $endBook->category_id, 'no_free' => $endBook->no, 'type' => $endBook->type,
                                'create_time' => $endBook->create_time, 'update_time' => $endBook->update_time];
                        }

                    }elseif ($type==5){

                        //热搜
                        $HotsearchBooks=Book::find()->where(['category_id'=> $ManIds,'is_end'=>1])->orderBy('search DESC')->limit(10)->all();


                        foreach ($HotsearchBooks as $hotsearchBook){
                            $result['data'][] = ['book_id' => $hotsearchBook->id, 'name' => $hotsearchBook->name,
                                'category' => $hotsearchBook->category->name, 'author' => $hotsearchBook->author->name,
                                'view' => $hotsearchBook->clicks, 'image' => HTTP_PATH .$hotsearchBook->image, 'size' => $hotsearchBook->size,
                                'score' => $hotsearchBook->score, 'intro' => $hotsearchBook->intro, 'is_end' =>$hotsearchBook->is_end,
                                'download' => $hotsearchBook->downloads, 'collection' => $hotsearchBook->collection, 'author_id' => $hotsearchBook->author_id,
                                'category_id' => $hotsearchBook->category_id, 'no_free' => $hotsearchBook->no, 'type' => $hotsearchBook->type,
                                'create_time' => $hotsearchBook->create_time, 'update_time' => $hotsearchBook->update_time];
                        }
                    }


                }elseif ($sex==2){

                    //查询属于男生的分类id
                    $ManIds=\Yii::$app->db->createCommand("SELECT id FROM category WHERE type=0")->queryColumn();
                    if($type==1){
                        //畅销

                        $SellingBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('collection DESC')->limit(10)->all();
                        foreach ( $SellingBooks as $sellingBook) {
                            $result['data'][] = ['book_id' => $sellingBook->id, 'name' => $sellingBook->name,
                                'category' => $sellingBook->category->name, 'author' => $sellingBook->author->name,
                                'view' => $sellingBook->clicks, 'image' => HTTP_PATH . $sellingBook->image, 'size' => $sellingBook->size,
                                'score' => $sellingBook->score, 'intro' => $sellingBook->intro, 'is_end' => $sellingBook->is_end,
                                'download' => $sellingBook->downloads, 'collection' => $sellingBook->collection, 'author_id' => $sellingBook->author_id,
                                'category_id' => $sellingBook->category_id, 'no_free' => $sellingBook->no, 'type' => $sellingBook->type,
                                'create_time' => $sellingBook->create_time, 'update_time' => $sellingBook->update_time];
                        }



                    }elseif ($type==2){

                        //新书

                        $NewBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('create_time DESC')->limit(10)->all();

                        foreach (  $NewBooks as $newBook) {
                            $result['data'][] = ['book_id' => $newBook->id, 'name' => $newBook->name,
                                'category' => $newBook->category->name, 'author' => $newBook->author->name,
                                'view' => $newBook->clicks, 'image' => HTTP_PATH . $newBook->image, 'size' => $newBook->size,
                                'score' => $newBook->score, 'intro' => $newBook->intro, 'is_end' =>$newBook->is_end,
                                'download' => $newBook->downloads, 'collection' => $newBook->collection, 'author_id' => $newBook->author_id,
                                'category_id' => $newBook->category_id, 'no_free' => $newBook->no, 'type' => $newBook->type,
                                'create_time' => $newBook->create_time, 'update_time' => $newBook->update_time];
                        }

                    }elseif ($type==3){

                        //热更

                        $UpdateBooks=Book::find()->where(['category_id'=> $ManIds])->orderBy('update_time DESC')->limit(10)->all();

                        foreach ( $UpdateBooks as $updateBook) {
                            $result['data'][] = ['book_id' => $updateBook->id, 'name' => $updateBook->name,
                                'category' => $updateBook->category->name, 'author' => $updateBook->author->name,
                                'view' => $updateBook->clicks, 'image' => HTTP_PATH . $updateBook->image, 'size' => $updateBook->size,
                                'score' => $updateBook->score, 'intro' => $updateBook->intro, 'is_end' =>$updateBook->is_end,
                                'download' => $updateBook->downloads, 'collection' => $updateBook->collection, 'author_id' => $updateBook->author_id,
                                'category_id' => $updateBook->category_id, 'no_free' => $updateBook->no, 'type' => $updateBook->type,
                                'create_time' => $updateBook->create_time, 'update_time' => $updateBook->update_time];
                        }


                    }elseif ($type==4){

                        //完结

                        $EndBooks=Book::find()->where(['category_id'=> $ManIds,'is_end'=>1])->orderBy('score DESC')->limit(10)->all();

                        foreach ($EndBooks as $endBook) {
                            $result['data'][] = ['book_id' => $endBook->id, 'name' => $endBook->name,
                                'category' => $endBook->category->name, 'author' => $endBook->author->name,
                                'view' => $endBook->clicks, 'image' => HTTP_PATH . $endBook->image, 'size' => $endBook->size,
                                'score' => $endBook->score, 'intro' => $endBook->intro, 'is_end' =>$endBook->is_end,
                                'download' => $endBook->downloads, 'collection' => $endBook->collection, 'author_id' => $endBook->author_id,
                                'category_id' => $endBook->category_id, 'no_free' => $endBook->no, 'type' => $endBook->type,
                                'create_time' => $endBook->create_time, 'update_time' => $endBook->update_time];
                        }

                    }elseif ($type==5){

                        //热搜
                        $HotsearchBooks=Book::find()->where(['category_id'=> $ManIds,'is_end'=>1])->orderBy('search DESC')->limit(10)->all();


                        foreach ($HotsearchBooks as $hotsearchBook){
                            $result['data'][] = ['book_id' => $hotsearchBook->id, 'name' => $hotsearchBook->name,
                                'category' => $hotsearchBook->category->name, 'author' => $hotsearchBook->author->name,
                                'view' => $hotsearchBook->clicks, 'image' => HTTP_PATH .$hotsearchBook->image, 'size' => $hotsearchBook->size,
                                'score' => $hotsearchBook->score, 'intro' => $hotsearchBook->intro, 'is_end' =>$hotsearchBook->is_end,
                                'download' => $hotsearchBook->downloads, 'collection' => $hotsearchBook->collection, 'author_id' => $hotsearchBook->author_id,
                                'category_id' => $hotsearchBook->category_id, 'no_free' => $hotsearchBook->no, 'type' => $hotsearchBook->type,
                                'create_time' => $hotsearchBook->create_time, 'update_time' => $hotsearchBook->update_time];
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