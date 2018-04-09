<?php
namespace frontend\controllers;
use backend\models\Book;
use backend\models\Chapter;
use backend\models\Purchased;
use libs\PostRequest;
use libs\Verification;
use yii\web\Response;
header("Access-Control-Allow-Origin:http://www.voogaa.cn");
class YuewenController extends \yii\web\Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //获取版权章节列表
    public function actionChapterList()
    {
        $result=[
            'flag'=>false,
            'content'=>[
                "totalcount"=>0,
                'totalpage'=>0,
            ]
        ];
        if (\Yii::$app->request->isPost) {
            //验证
            //$obj = new Verification();
            //$res = $obj->check();
            //if($res){
            // $result['msg']= $res;
           // }else{
            //接收手机端传递参数
            $user_id = \Yii::$app->request->post('user_id');//用户id
            $book_id = \Yii::$app->request->post('book_id');//本地图书id
            $min_chapter_id = \Yii::$app->request->post('min_chapter_id ');//起始章节 ID 默认为 0(不含该章)
            $page_now = \Yii::$app->request->post('page_now');//分页显示时，为当前第几页，默认为 1
            $page_size = \Yii::$app->request->post('page_size');//分页显示时，为每页大小，默认全部显示

            //判断用户是否传入书id和用户id
            if(empty($user_id) || empty($book_id)){
                $result['code']=400;
                $result['msg']='未传入指定参数';
                return $result;
            }

            //查询该书从什么章节开始开始收费
            $book = Book::findOne(['id' => $book_id]);
            //该书观看数加1
            $book->clicks=$book->clicks+1;
            $book->real_read=$book->real_read+1;
            $book->save();
            ////////////////////获取凯兴章节列表//////////////////////////
            if($book->ascription==1){

                //请求地址
                $postUrl = 'http://partner.chuangbie.com/partner/chapterlist';
                $curlPost = [
                    'partner_id' => 2130,
                    'partner_sign' => 'b42c36ddd1a5cc2c6895744143f77b7b',
                    'book_id' => $book->copyright_book_id,
                    'min_chapter_id ' => $min_chapter_id,
                    'page_now' => $page_now,
                    'page_size' => $page_size,
                ];

                $post = new PostRequest();
                $data = $post->request_post($postUrl, $curlPost);
                $data = json_decode($data, true);
                //统计数组长度
                $ArrayLength=count($data['content']['data']);
                //判断该书是否收费书
                if($book->is_free==0){
                    //免费书is_vip全部改成0
                    for ($i=0;$i<$ArrayLength;$i++){
                        $data['content']['data'][$i]['is_vip']=0;
                        //加入从多少章节开始收费
                        $data['content']['data'][$i]['no']=$book->no;
                    }
                    return $data;
                }else{
                    //查询用户已购书
                    $purchased=Purchased::findOne(['user_id'=>$user_id,'book_id'=>$book_id]);
                    if($purchased){
                        $chapter_no=explode('|',$purchased->chapter_no);//分割成数组
                        $chapter_no=array_filter($chapter_no);//删除数组中空元素
                    }else{
                        //没有购买书
                        $chapter_no=[];
                    }

                    //循环更改is_vip和加入no(从多少章节开始收费)
                    for ($i=0;$i<$ArrayLength;$i++){
                        //把全部章节更改为收费章节
                        $data['content']['data'][$i]['is_vip']=1;
                        //加入从多少章节开始收费
                        $data['content']['data'][$i]['no']=$book->no;
                        //判断用户是否已购买该章节,该书从多少章节开始收费.用户购买该章节或者该章节是免费章节,is_vip改成0
                        if(in_array(($i+1),$chapter_no) || (($i+1)<$book->no && $book->no!=0) ){
                            $data['content']['data'][$i]['is_vip']=0;

                        }
                    }
                    return $data;

                }
                ////////////////////获取凯兴章节列表结束//////////////////////////




                //=======================获取17k小说网章节列表=====================

            }elseif ($book->ascription==4){


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
                //return $datas->data->volumes;
                foreach ($datas->data->volumes as $rows){
                    foreach ($rows->chapters as $row){
                        //var_dump($row->name);
                        $result['flag']=true;
                        $result['content']['data'][]=
                            [
                                'chapter_id'=>$row->id,
                                'chapter_name'=>$row->name,
                                'book_id'=>$book_id,
                                'vname'=>'第一卷',
                                'volume_id'=>$row->volume_id,
                                'is_vip'=>0,
                                'sortid'=>$row->id,
                                'word_count'=>$row->word_count,
                                'update_time'=>$row->updated_at,
                                'no'=>0
                            ];
                        $result['msg']='成功返回章节信息';
                    }
                }
                //=========================获取17k小说章节结束=============================


                //////////////////////////本地图书章节列表//////////////////////////
            }else{
                //分表id
                $re=Chapter::resetPartitionIndex($book_id);
                if($re!=0){
                    //查询该书章节列表
                    $chapters=Chapter::find()->where(['book_id'=>$book_id])->orderBy('no ASC')->all();
                    $ArrayLength=count($chapters);
                    //该书免费
                    if($book->is_free==0){
                        for ($i=0;$i<$ArrayLength;$i++){
                            //var_dump($chapter);exit;
                            $result['flag']=true;
                            $result['content']['data'][]=
                                [
                                    'chapter_id'=>$chapters[$i]->id,
                                    'chapter_name'=>$chapters[$i]->chapter_name,
                                    'book_id'=>$chapters[$i]->book_id,
                                    'vname'=>'第一卷',
                                    'volume_id'=>0,
                                    'is_vip'=>0,
                                    'sortid'=>$chapters[$i]->no,
                                    'word_count'=>$chapters[$i]->word_count,
                                    'update_time'=>$chapters[$i]->update_time,
                                    'no'=>$book->no
                                ];
                            $result['msg']='成功返回章节信息';
                        }
                    }else{
                        //该书收费书
                        //查询用户已购书
                        $purchased=Purchased::findOne(['user_id'=>$user_id,'book_id'=>$book_id]);
                        if($purchased){
                            $chapter_no=explode('|',$purchased->chapter_no);//分割成数组
                            $chapter_no=array_filter($chapter_no);//删除数组中空元素
                        }else{
                            //没有购买书
                            $chapter_no=[];
                        }
                        for($i=0;$i<$ArrayLength;$i++){
                            //把全部章节更改为收费章节

                            //判断用户是否已购买该章节,该书从多少章节开始收费.用户购买该章节或者该章节是免费章节,is_vip改成0
                            if(in_array(($i+1),$chapter_no) || (($i+1)<$book->no && $book->no!=0)){
                                $result['flag']=true;
                                $result['content']['data'][]=
                                    [
                                        'chapter_id'=>$chapters[$i]->id,
                                        'chapter_name'=>$chapters[$i]->chapter_name,
                                        'book_id'=>$chapters[$i]->book_id,
                                        'vname'=>'第一卷',
                                        'volume_id'=>0,
                                        'is_vip'=>0,
                                        'sortid'=>$chapters[$i]->no,
                                        'word_count'=>$chapters[$i]->word_count,
                                        'update_time'=>$chapters[$i]->update_time,
                                        'no'=>$book->no,
                                    ];
                                $result['msg']='成功返回章节信息';



                            }else{
                                $result['flag']=true;
                                $result['content']['data'][]=
                                    [
                                        'chapter_id'=>$chapters[$i]->id,
                                        'chapter_name'=>$chapters[$i]->chapter_name,
                                        'book_id'=>$chapters[$i]->book_id,
                                        'vname'=>'第一卷',
                                        'volume_id'=>0,
                                        'is_vip'=>1,
                                        'sortid'=>$chapters[$i]->no,
                                        'word_count'=>$chapters[$i]->word_count,
                                        'update_time'=>$chapters[$i]->update_time,
                                        'no'=>$book->no,
                                    ];
                                $result['msg']='成功返回章节信息';
                            }
                        }
                    }

                }else{
                    $result['msg']='无可操作数据表';
                }




            }

           //  }

        }else {
            $result['code'] = 400;
            $result['msg'] = '请求方式错误';
        }

      return $result;
    }

    //获取章节内容
    public function actionChapterContent(){
        $result=[
            'flag'=>false,
            'content'=>[],
        ];
        if(\Yii::$app->request->isPost){
            //验证
            $obj=new Verification();
            $res=$obj->check();
          // if($res){
               // $result['msg']= $res;
           // }else{

                //接收手机端传递参数
                $book_id=\Yii::$app->request->post('book_id');//本地图书id
                $copyright_chapter_ids=\Yii::$app->request->post('copyright_chapter_id');//版权书章节id
                //var_dump($copyright_chapter_ids);exit;
                //解析json
                $copyright_chapter_ids=json_decode($copyright_chapter_ids);
                //查找该本书
                $book=Book::findOne(['id'=>$book_id]);
                $book->downloads=$book->downloads+1;
                $book->save();

                //遍历获取多章节内容
                $datas=[];
                if($book->ascription==1){
                    ///////////////////////////////凯兴图书内容//////////////////////////////////////
                    //请求地址
                    $postUrl = 'http://partner.chuangbie.com/partner/chaptercontent';
                    foreach ( $copyright_chapter_ids->copyright_chapter_id as  $copyright_chapter_id){
                        $curlPost =[
                            'partner_id'=>2130,
                            'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                            'book_id'=>$book->copyright_book_id,
                            'chapter_id'=>$copyright_chapter_id,
                        ];
                        $post=new PostRequest();
                        $datas[]=json_decode($post->request_post($postUrl,$curlPost));

                    }
                    return $datas;
                    ////////////////////////////凯兴内容结束////////////////////////////

                }elseif($book->ascription==4){
                    //////////////////////////17k小说网章节内容//////////////////////////////////////
                    foreach ($copyright_chapter_ids->copyright_chapter_id as  $copyright_chapter_id){
                        $get=new PostRequest();
                        $contents=$get->send_request('http://api.17k.com/v2/book/'.$book->copyright_book_id.'/chapter/'.$copyright_chapter_id.'/content',

                            [
                                '_access_version'=>2,
                                '_versions'=>958,
                                'access_token'=>'',
                                'app_key'=>2222420362,
                            ]
                        );
                        $contents=(json_decode($contents));
                        //var_dump($contents->data->content);exit;

                        $result['flag']=true;
                        $result['content']['data']['chapter_content']=$contents->data->content;
                        $result['msg']='成功返回章节内容';
                        $datas[]=$result;
                        //var_dump($contents->data->content);exit;
                    }
                    return $datas;
                    ////////////////////////////17k章节内容结束/////////////////////////

                }else{
                    //分区id
                    $re=Chapter::resetPartitionIndex($book_id);
                    if($re!=0){
                        //==============================本地图书章节内容================================
                        foreach ($copyright_chapter_ids->copyright_chapter_id as  $copyright_chapter_id){
                            $model=Chapter::find()->where(['id'=>$copyright_chapter_id])->one();
                            $string=file_get_contents(BOOK_PATH.$model->path);
                            $result['flag']=true;
                            $result['content']['data']['chapter_content']=$string;
                            $result['msg']='成功返回章节内容';
                            $datas[]=$result;
                        }
                        return $datas;
                    }else{
                        $result['msg']='无可操作数据表';
                    }

                }


          // }

        }else{
            $result['code']=400;
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //获取版权图书列表
    public function actionIndex(){
        $result = [
            'flag'=>false,
        ];
        if(\Yii::$app->request->isPost){
            $books=Book::find()->where(['from'=>3])->all();
            foreach ($books as $book){
                $result['data'][]=['book_id'=>$book->id,'name'=>$book->name,
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
                $result['flag']=true;
                $result['msg']='获取图书列表成功';
            }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

}