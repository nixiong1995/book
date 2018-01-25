<?php
namespace frontend\controllers;
use backend\models\Book;
use backend\models\Purchased;
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

    //多章购买
    public function actionMultiChapter(){
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
            $chapter_number=\Yii::$app->request->post('chapter_number');//章节数量
            $user_id=\Yii::$app->request->post('user_id');//用户id

            //检测是否传入指定参数
            if(empty($book_id) || empty($user_id)){
                $relust['msg']='请传入指定参数';
                return $relust;
            }

            //查询该书价格以及出处和从多少章节开始收费
            $book=Book::findOne(['id'=>$book_id]);
            //查询用户是否已购买该书章节
            $purchased=Purchased::find()->where(['user_id'=>$user_id,'book_id'=>$book_id])->one();
            //获取版权书章节列表,计算购买章节字数
            $postUrl = 'http://partner.chuangbie.com/partner/chapterlist';
            $curlPost =[
                'partner_id'=>2130,
                'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                'book_id'=>$book->copyright_book_id,
            ];
            $post=new PostRequest();
            $records=json_decode($post->request_post($postUrl,$curlPost));
            $total_chapter=count($records->content->data);//该书总章节数
            $word_count=0;//字数

            //判断是否传入购买章节数
            if(empty($chapter_number)){

                //购买整本书或者购买剩余所有章节
                //判断是否已购买该书
                $no=0;//已购章节数量
                if($purchased){
                    //已购买该书
                    $chapter_no=explode('|',$purchased->chapter_no);
                    //最大章节(也就是最后购买章节)
                    $chapter_no=array_filter($chapter_no);//去除数组空元素
                    $no=count($chapter_no);//统计购买章节数量
                    $new_chapter_no=max($chapter_no);//最新章节号


                    //循环获取章节字数
                    for ($i=$new_chapter_no;$i<$total_chapter;$i++){
                        $word_count+=$records->content->data[$i]->word_count;
                    }

                }else{
                    //用户没有购买过该书,默认从收费章节开始购买
                    $charge=$book->no-1;//在数组中开始收费章节
                    for ($i=$charge;$i<$total_chapter;$i++){
                        // $datas[]=$records->content->data[$i]->word_count;
                        $word_count+=$records->content->data[$i]->word_count;

                    }
                }


                //计算购书价格
                $price=round($book->price*($word_count/1000));
                $RealPrice=$price;//实际价格
                $discount=0;//折扣



                //图书折扣
                if(($total_chapter-$no)>=20 && ($total_chapter-$no)<60){//购买20章98折
                    $price=$price*0.98;
                    $discount=0.98;
                    //var_dump($price);exit;
                }elseif (($total_chapter-$no)>=60 && ($total_chapter-$no)<100){//购买60章9折
                    $price=$price*0.9;
                    $discount=0.9;
                }elseif (($total_chapter-$no)>100){//购买100章以上
                    $price=$price*0.8;
                    $discount=0.8;
                }
                //查询用户账户
                $user=User::findOne(['id'=>$user_id]);
                //判断用户账户是否书券
                //定义抵扣书券为0
                $voucher=0;
                if($user->voucher){
                    //计算书券抵扣金额(最多只能抵扣25%)
                    $deduction=round($price*0.25);//最多抵扣的金额
                    if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                        $voucher=$deduction.'.00';

                    }else{//如果账户书券小于最多抵扣书券,减去账户所有书券
                        $voucher=$user->voucher;
                    }
                }
                //最终价格
                $DiscountedPrice=round($price-$voucher);
                //计算用户账户余额加书券是否大于图书价格
                if($user->ticket<$price){
                    $relust['code']=401;
                    $relust['msg']='余额不足';
                    return $relust;
                }else{
                    $relust['code']=200;
                    $relust['data']=['RealPrice'=>$RealPrice.'.00','discount'=>$discount,'deduction'=>$voucher,'DiscountedPrice'=>$DiscountedPrice.'.00'];
                    $relust['msg']='结算价格计算成功';

                }


            }else{
                if($purchased){
                    //用户已经购买过该书章节,这次购买默认根据最后购买的章节购买
                    //将已购章节字符串分割成数组
                    $chapter_no=explode('|',$purchased->chapter_no);
                    //最大章节(也就是最后购买章节)
                    $new_chapter_no=max($chapter_no);
                    //var_dump($new_chapter_no);exit;
                    //循环获取章节字数
                    for ($i=$new_chapter_no;$i<($chapter_number+$new_chapter_no);$i++){
                        $word_count+=$records->content->data[$i]->word_count;
                    }


                }else{
                    //用户没有购买过该书,默认从收费章节开始购买
                    $charge=$book->no-1;//在数组中开始收费章节
                    for ($i=$charge;$i<($chapter_number+$charge);$i++){
                        // $datas[]=$records->content->data[$i]->word_count;
                        $word_count+=$records->content->data[$i]->word_count;

                    }
                }
                //var_dump(  $word_count);exit;
                //计算购书价格
                $price=round($book->price*($word_count/1000));
                $RealPrice=$price;//实际价格
                $discount=0;//折扣


                //图书折扣
                if($chapter_number>=20 &&$chapter_number<60){//购买20章98折
                    $price=$price*0.98;
                    $discount=0.98;
                    //var_dump($price);exit;
                }elseif ($chapter_number>=60 && $chapter_number<100){//购买60章9折
                    $price=$price*0.9;
                    $discount=0.9;
                }elseif ($chapter_number>=100){//购买100章以上
                    $price=$price*0.85;
                    $discount=0.85;
                }
                //查询用户账户
                $user=User::findOne(['id'=>$user_id]);
                //判断用户账户是否书券
                //定义抵扣书券为0
                $voucher=0;
                if($user->voucher){
                    //计算书券抵扣金额(最多只能抵扣25%)
                    $deduction=round($price*0.25);//最多抵扣的金额
                    if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                        $voucher=$deduction.'.00';

                    }else{//如果账户书券小于最多抵扣书券,减去账户所有书券
                        $voucher=$user->voucher;
                    }
                }
                //最终价格
                $DiscountedPrice=round($price-$voucher);

                //计算用户账户余额加书券是否大于图书价格
                if($user->ticket<$price){
                    $relust['code']=401;
                    $relust['msg']='余额不足';
                    return $relust;
                }else{
                    $relust['code']=200;
                    $relust['data']=['RealPrice'=>$RealPrice.'.00','discount'=>$discount,'deduction'=>$voucher,'DiscountedPrice'=>$DiscountedPrice.'.00'];
                    $relust['msg']='结算价格计算成功';

                }


            }

            // }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //单章购买计算
    public function actionSingleChapter(){
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
                $chapter_id=\Yii::$app->request->post('chapter_id');//章节id
                $user_id=\Yii::$app->request->post('user_id');//用户id
                //检测是否传入指定参数
                if(empty($book_id) || empty($chapter_id) || empty($user_id)){
                    $relust['msg']='请传入指定参数';
                    return $relust;
                }

                //查询该书价格以及出处
                $book=Book::findOne(['id'=>$book_id]);
                //根据版权章节id查询出章节字数
                //请求地址

                $postUrl = 'http://partner.chuangbie.com/partner/chapterinfo';
                //遍历查询章节字数

                $curlPost =[
                    'partner_id'=>2130,
                    'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                    'book_id'=>$book->copyright_book_id,
                    'chapter_id'=>$chapter_id,
                ];
                $post=new PostRequest();
                $record=json_decode($post->request_post($postUrl,$curlPost));
                //var_dump($record);exit;


                $word_count=$record->content->data->word_count;//字数
                //计算购书价格
                $price=round($book->price*($word_count/1000));
                $RealPrice=$price;//实际价格

                //查询用户账户
                $user=User::findOne(['id'=>$user_id]);
                //判断用户账户是否书券
                //定义抵扣书券为0
                $voucher=0;
                if($user->voucher){
                    //计算书券抵扣金额(最多只能抵扣25%)
                    $deduction=round($price*0.25);//最多抵扣的金额
                    if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                        $voucher=$deduction.'.00';

                    }else{//如果账户书券小于最多抵扣书券,减去账户所有书券
                        $voucher=$user->voucher;
                    }
                }
                //最终价格
                $DiscountedPrice=round($price-$voucher);

                //计算用户账户余额加书券是否大于图书价格
                if($user->ticket<$price){
                    $relust['code']=401;
                    $relust['msg']='余额不足';
                    return $relust;
                }else{
                    $relust['code']=200;
                    $relust['data']=['RealPrice'=>$RealPrice.'.00','discount'=>0,'deduction'=>$voucher,'DiscountedPrice'=>$DiscountedPrice.'.00'];
                    $relust['msg']='结算价格计算成功';

                }













           // }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //消费扣除

}