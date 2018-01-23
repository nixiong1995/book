<?php
namespace frontend\controllers;
use backend\models\Book;
use backend\models\User;
use DeepCopy\f001\B;
use libs\PostRequest;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

//消费接口

class  ConsumeController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionIndex(){
        $relust=[
          'code'=>400,
          'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            //验证
            $obj = new Verification();
            $res = $obj->check();
          //  if($res){
            // $result['msg']= $res;
           // }else{
                //接收手机端传递的参数
                $book_id=\Yii::$app->request->post('book_id');//图书id
                $chapter_ids=\Yii::$app->request->post('chapter_id');//章节id
                $user_id=\Yii::$app->request->post('user_id');//用户id
                //处理章节参数字符串
                $chapter_ids=explode(',',$chapter_ids);
                //删除数组中空元素
                $chapter_ids=array_filter($chapter_ids);
                //检测是否传入指定参数
                if(empty($book_id) || empty($chapter_ids) || empty($user_id)){
                    $relust['msg']='请传入指定参数';
                    return $relust;
                }

                //查询该书价格以及出处
                $book=Book::findOne(['id'=>$book_id]);
                //根据版权章节id查询出章节字数
                //请求地址

                $postUrl = 'http://partner.chuangbie.com/partner/chapterinfo';
                //遍历查询章节字数
                $records=[];
                foreach ($chapter_ids as $chapter_id){
                    $curlPost =[
                        'partner_id'=>2130,
                        'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                        'book_id'=>$book->copyright_book_id,
                        'chapter_id'=>$chapter_id,
                    ];
                    $post=new PostRequest();
                    $records[]=json_decode($post->request_post($postUrl,$curlPost));
                }

                //查询该书总章节数
                $postUrl2='http://partner.chuangbie.com/partner/chapterlist';
                $curlPost2=[
                    'partner_id'=>2130,
                    'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                    'book_id'=>$book->copyright_book_id,
                ];
                $post2=new PostRequest();
                $records2=json_decode($post2->request_post($postUrl2,$curlPost2));
                $total_chapter=count($records2->content->data);//该书总章节数



                $word_count=0;//字数
                //遍历得到每章字数
                foreach ($records as $record){
                    //累加章节字数
                    $word_count+=$record->content->data->word_count;

                }
                //统计用户购买了多少章节
                $number=count($chapter_ids);
                //计算购书价格
                $price=$book->price*($word_count/1000);


                //图书折扣
                if($number>=20 &&$number<60){//购买20章98折
                    $price=$price*0.98;
                    //var_dump($price);exit;
                }elseif ($number>=60 && $number<100){//购买60章9折
                    $price=$price*0.9;
                }elseif ($number>=100){//购买100章以上
                    $price=$price*0.85;
                }elseif ($number==$total_chapter){//购买所有章节
                    $price=$price*0.8;
                }
                //查询用户账户
                $user=User::findOne(['id'=>$user_id]);
                //判断用户账户是否书券
                //定义抵扣书券为0
                $voucher=0;
                if($user->voucher){
                    //计算书券抵扣金额(最多只能抵扣25%)
                    $deduction=$price*0.25;//最多抵扣的金额
                    if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                        $voucher=$deduction;

                    }else{//如果账户书券小于最多抵扣书券,减去账户所有书券
                        $voucher=$user->voucher;
                    }
                }
                //最终价格
                $price=round($price-$voucher);

                //计算用户账户余额加书券是否大于图书价格
                if($user->ticket<$price){
                    $relust['code']=401;
                    $relust['msg']='余额不足';
                    return $relust;
                }else{
                    $relust['code']=200;
                    $relust['data'][]=['price'=>$price,'voucher'=>$voucher];
                    $relust['msg']='结算价格计算成功';

                }













           // }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }
}