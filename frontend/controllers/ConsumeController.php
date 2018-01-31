<?php
namespace frontend\controllers;
use backend\models\Book;
use backend\models\Consume;
use backend\models\Purchased;
use backend\models\User;
use DeepCopy\f001\B;
use libs\PostRequest;
use libs\Verification;
use yii\db\Exception;
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

    //多章价格计算
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
            //查询用户账户
            $user=User::findOne(['id'=>$user_id]);
            if(!$user){
                $relust['msg']='没有该用户';
                return $relust;
            }

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
            $total_chapter=count($records->content->data);//该书总章节数(用于循环计算章节字数)
            $word_count=0;//字数

            //判断是否传入购买章节数(没有传入是购买剩余所有章节或者购买整本书)
            if(empty($chapter_number)){

                //购买整本书或者购买剩余所有章节
                $no=0;//定义已购章节数量
                //判断用户是否已购买该书
                if($purchased){
                    //购买剩余所有章节
                    $chapter_no=explode('|',$purchased->chapter_no);  //已购买该书的数组
                    $chapter_no=array_filter($chapter_no);//去除数组空元素
                    $no=count($chapter_no);//统计用户已经购买章节数量,确定现在购买章节数量,计算折扣
                    $new_chapter_no=max($chapter_no); //最大章节(也就是最后购买章节),计算用户这次该从哪个章节购买
                    //获取购买的起始章节名称
                    $chapter_name=$records->content->data[$new_chapter_no]->chapter_name;
                    //循环获取章节字数
                    for ($i=$new_chapter_no;$i<$total_chapter;$i++){
                        $word_count+=$records->content->data[$i]->word_count;
                    }

                }else{
                    //购买整本书
                    //用户没有购买过该书,默认从收费章节开始购买
                    $charge=$book->no-1;//在数组中开始收费章节,
                    //获取章节起始购买名称
                    $chapter_name=$records->content->data[$charge]->chapter_name;
                    //循环统计购买章节字数
                    for ($i=$charge;$i<$total_chapter;$i++){
                        $word_count+=$records->content->data[$i]->word_count;
                    }
                }


                //计算购书价格
                $price=round($book->price*($word_count/1000));
                $RealPrice=$price;//实际价格
                $discount=0;//定义折扣后价格

                //图书折扣
                if(($total_chapter-$no)<20){//购买20章以下无折扣
                    $discount=round($price*1);
                }elseif(($total_chapter-$no)>=20 && ($total_chapter-$no)<60){//购买20章98折
                    $discount=round($price*0.98);
                }elseif (($total_chapter-$no)>=60 && ($total_chapter-$no)<100){//购买60章9折
                    $discount=round($price*0.9);
                }elseif (($total_chapter-$no)>100){//购买100章以上8折
                    $discount=round($price*0.8);
                }
                //判断用户账户是否有书券
                //定义抵扣书券为0
                $voucher=0;
                if($user->voucher>0){
                    //计算书券抵扣金额(最多只能抵扣25%)
                    $deduction=round($discount*0.25);//最多抵扣的金额

                    if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                        $voucher=$deduction;
                        //账户书券余额
                        //$voucher_balance=$user->voucher-$voucher;

                    }else{//如果账户书券小于最多抵扣书券,减去账户所有书券
                        $voucher=round($user->voucher);
                        //$voucher_balance=0;
                    }
                }

                //最终价格
                $DiscountedPrice=round($discount-$voucher);
                //账户阅票余额
               // $ticket_balance=$user->ticket-$DiscountedPrice;
                $ticket_balance=round($user->ticket);
                //账户书券余额
                $voucher_balance=round($user->voucher);
                /*if($ticket_balance>0){
                    $ticket_balance=$ticket_balance;
                }else{
                    $ticket_balance=0.00;
                }*/

                //计算用户账户余额加书券是否大于图书价格
                if($user->ticket<$DiscountedPrice){
                    $relust['code']=401;
                    $relust['msg']='余额不足';
                    $relust['data']=['RealPrice'=>$RealPrice,'discount'=>$discount,'deduction'=>$voucher,'DiscountedPrice'=>$DiscountedPrice,'VoucherBalance'=>$voucher_balance,'TicketBalance'=>$ticket_balance,'chapter_name'=>$chapter_name];
                    return $relust;
                }else{
                    $relust['code']=200;
                    $relust['data']=['RealPrice'=>$RealPrice,'discount'=>$discount,'deduction'=>$voucher,'DiscountedPrice'=>$DiscountedPrice,'VoucherBalance'=>$voucher_balance,'TicketBalance'=>$ticket_balance,'chapter_name'=>$chapter_name];
                    $relust['msg']='结算价格计算成功';

                }


            }else{
                //根据传入的章节数量计算价格
                if($purchased){//用户已经购买了该章节
                    //将已购章节字符串分割成数组
                    $chapter_no=explode('|',$purchased->chapter_no);
                    //统计已购买章节数
                    //删除数组空元素
                    $chapter_no=array_filter($chapter_no);//删除数组空元素
                    $QuantityPurchased=count($chapter_no);//统计已购买章节
                    //计算免费章节数和已购买章节数
                    $num=($book->no-1)+$QuantityPurchased;
                    //剩余未购买的章节数
                    $Surplus=$total_chapter-$num;
                    //最大章节(也就是最后购买章节)
                    $new_chapter_no=max($chapter_no);
                    if($chapter_number>$Surplus){
                        $chapter_number=$Surplus;
                    }
                    if($Surplus==0){
                        $new_chapter_no=$new_chapter_no-1;//用户已购买完所有章节,没有下个章节.显示章节名称为最后章节
                    }
                    //var_dump($new_chapter_no);exit;
                    //用户购买章节的起始章节名称
                    $chapter_name=$records->content->data[$new_chapter_no]->chapter_name;
                    //循环获取章节字数
                    for ($i=$new_chapter_no;$i<($chapter_number+$new_chapter_no);$i++){
                        $word_count+=$records->content->data[$i]->word_count;
                    }

                }else{
                    //用户没有购买过该书,默认从收费章节开始购买
                    $charge=$book->no-1;//在数组中开始收费章节
                    //剩余未购买章节数
                    $Surplus=$total_chapter-$charge;
                    //如果用户选择购买章节大于本书章节,默认本书最大章节
                    if($chapter_number>$Surplus){
                        $chapter_number=$Surplus;
                    }
                    //用户购买章节的起始章节名称
                    $chapter_name=$records->content->data[$charge]->chapter_name;
                    //循环计算购买章节字数
                    for ($i=$charge;$i<($chapter_number+$charge);$i++){

                        $word_count+=$records->content->data[$i]->word_count;

                    }
                }

                //计算购书价格
                $price=round($book->price*($word_count/1000));
                $RealPrice=$price;//实际价格
                $discount=0;//定义折扣后价格

                //图书折扣
                if($Surplus>=$chapter_number){
                    if($chapter_number<20){
                        $discount=round($price*1);
                    }elseif($chapter_number>=20 && $chapter_number<60){//购买20章98折
                        $discount=round($price*0.98);
                    }elseif ($chapter_number>=60 && $chapter_number<100){//购买60章9折
                        $discount=round($price*0.9);
                    }elseif ($chapter_number>=100){//购买100章以上
                        $discount=round($price*0.85);
                    }
                }else{
                    if($Surplus<20){
                        $discount=round($price*1);
                    }elseif($Surplus>=20 && $Surplus<60){//购买20章98折
                        $discount=round($price*0.98);
                    }elseif ($Surplus>=60 && $Surplus<100){//购买60章9折
                        $discount=round($price*0.9);
                    }elseif ($Surplus>=100){//购买100章以上
                        $discount=round($price*0.85);
                    }
                }



                //判断用户账户是否书券
                //定义抵扣书券为0
                $voucher=0;
                if($user->voucher>0){
                    //计算书券抵扣金额(最多只能抵扣25%)
                    $deduction=round($discount*0.25);//最多抵扣的金额


                    if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                        $voucher=$deduction;
                        //用户书券余额
                        //$voucher_balance=$user->voucher-$voucher;

                    }else{//如果账户书券小于最多抵扣书券,减去账户所有书券
                        $voucher=round($user->voucher);
                        //账户书券余额
                        //$voucher_balance=0.00;
                    }
                }
                //最终价格
                $DiscountedPrice=round($discount-$voucher);
                //用户阅票余额
               // $ticket_balance=$user->ticket-$DiscountedPrice;
                $ticket_balance=round($user->ticket);
                //账户书券余额
                $voucher_balance=round($user->voucher);
               /* if($ticket_balance>0){
                    $ticket_balance=$ticket_balance;
                }else{
                    $ticket_balance=0.00;
                }*/


                //计算用户账户余额加书券是否大于图书价格
                if($user->ticket< $DiscountedPrice){
                    $relust['code']=401;
                    $relust['data']=['RealPrice'=>$RealPrice,'discount'=>$discount,'deduction'=>$voucher,'DiscountedPrice'=>$DiscountedPrice,'VoucherBalance'=>$voucher_balance,'TicketBalance'=>$ticket_balance,'chapter_name'=>$chapter_name];
                    $relust['msg']='余额不足';
                    return $relust;
                }else{
                    $relust['code']=200;
                    $relust['data']=['RealPrice'=>$RealPrice,'discount'=>$discount,'deduction'=>$voucher,'DiscountedPrice'=>$DiscountedPrice,'VoucherBalance'=>$voucher_balance,'TicketBalance'=>$ticket_balance,'chapter_name'=>$chapter_name];
                    $relust['msg']='结算价格计算成功';

                }
            }

            // }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //单章价格计算
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
            //查询用户账户
            $user=User::findOne(['id'=>$user_id]);
            if(!$user){
                $relust['msg']='没有该用户';
                return $relust;
            }
                //根据版权章节id查询出章节字数
                //请求地址
                $postUrl = 'http://partner.chuangbie.com/partner/chapterinfo';
                $curlPost =[
                    'partner_id'=>2130,
                    'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                    'book_id'=>$book->copyright_book_id,
                    'chapter_id'=>$chapter_id,
                ];

                $post=new PostRequest();
                $record=json_decode($post->request_post($postUrl,$curlPost));

                $word_count=$record->content->data->word_count;//购买章节字数
                //计算购书价格
                $price=round($book->price*($word_count/1000));
                $RealPrice=$price;//实际价格

                //判断用户账户是否有书券
                //定义抵扣书券为0
                $voucher=0;
                if($user->voucher){
                    //计算书券抵扣金额(最多只能抵扣25%)
                    $deduction=round($price*0.25);//最多抵扣的金额
                    if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                        $voucher=$deduction;
                        //账户书券余额
                        //$voucher_balance=$user->voucher-$voucher;


                    }else{//如果账户书券小于最多抵扣书券,减去账户所有书券
                        $voucher=round($user->voucher);
                        //账户书券余额
                       // $voucher_balance=0.00;
                    }
                }
                //最终价格
                $DiscountedPrice=round($price-$voucher);
                //账户阅票余额
                //$ticket_balance=$user->ticket-$DiscountedPrice;
                  $ticket_balance=round($user->ticket);
                //账户书券余额
                  $voucher_balance=round($user->voucher);
                /*if($ticket_balance>0){
                    $ticket_balance=$ticket_balance;
                }else{
                    $ticket_balance=0.00;
                }*/

                //计算用户账户余额加书券是否大于图书价格
                if($user->ticket<$DiscountedPrice){
                    $relust['code']=401;
                    $relust['msg']='余额不足';
                    $relust['data']=['RealPrice'=>$RealPrice,'discount'=>$RealPrice,'deduction'=>$voucher,'DiscountedPrice'=>$DiscountedPrice,'VoucherBalance'=>$voucher_balance,'TicketBalance'=>$ticket_balance];
                    return $relust;
                }else{
                    $relust['code']=200;
                    $relust['data']=['RealPrice'=>$RealPrice,'discount'=>$RealPrice,'deduction'=>$voucher,'DiscountedPrice'=>$DiscountedPrice,'VoucherBalance'=>$voucher_balance,'TicketBalance'=>$ticket_balance];
                    $relust['msg']='结算价格计算成功';
                }

           // }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //多章购买
    public function actionMultiEmption(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            $obj = new Verification();
            $res = $obj->check();
           // if($res){
               // $result['msg']= $res;

          //  }else{
                //接收手机端传递的参数
                $book_id=\Yii::$app->request->post('book_id');//图书id
                $chapter_number=\Yii::$app->request->post('chapter_number');//章节数量
                $user_id=\Yii::$app->request->post('user_id');//用户id
                $ticket=\Yii::$app->request->post('ticket');//消费阅票数量
                $BookCoupons=\Yii::$app->request->post('voucher');//消费书券数量

                //检测是否传入指定参数
                if(!isset($book_id )|| !isset($ticket) || !isset($user_id) || !isset($BookCoupons)){
                    $relust['msg']='请传入指定参数';
                    return $relust;
                }

                //查询该书价格以及出处和从多少章节开始收费
                $book=Book::findOne(['id'=>$book_id]);
                //查询用户账户
                $user=User::findOne(['id'=>$user_id]);
                if(!$user){
                    $relust['msg']='没有该用户';
                    return $relust;
                }
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
                $total_chapter=count($records->content->data);//该书总章节数(用于循环计算章节字数)
                $word_count=0;//字数
                $str='';//定义这次购买的章节号

                //判断是否传入购买章节数(没有传入是购买剩余所有章节或者购买整本书)
                if(empty($chapter_number)){

                    //购买整本书或者购买剩余所有章节
                    $no=0;//定义已购章节数量

                    //判断用户是否已购买该书
                    if($purchased){
                        //购买剩余所有章节
                        $chapter_no=explode('|',$purchased->chapter_no);  //已购买该书的数组
                        $chapter_no=array_filter($chapter_no);//去除数组空元素
                        $no=count($chapter_no);//用户已购章节数;统计用户已经购买章节数量,确定现在购买章节数量,计算折扣
                        $new_chapter_no=max($chapter_no); //最大章节(也就是最后购买章节),计算用户这次该从哪个章节购买
                        //循环获取章节字数以及拼接购买的章节号
                        for ($i=$new_chapter_no;$i<$total_chapter;$i++){
                            $word_count+=$records->content->data[$i]->word_count;
                            $str.=$records->content->data[$i]->sortid.'|';//拼接这次购买章节号
                        }

                    }else{
                        //购买整本书
                        //用户没有购买过该书,默认从收费章节开始购买
                        $charge=$book->no-1;//在数组中开始收费章节,
                        //循环统计购买章节字数
                        for ($i=$charge;$i<$total_chapter;$i++){
                            $word_count+=$records->content->data[$i]->word_count;
                            $str.=$records->content->data[$i]->sortid.'|';//拼接这次购买章节号
                        }
                    }

                    //计算购书价格
                    $price=round($book->price*($word_count/1000));
                    $RealPrice=$price;//实际价格
                    $discount=0;//定义折扣

                    //图书折扣
                    if(($total_chapter-$no)<20){//购买20章以下无折扣
                        $price=round($price*1);
                        $discount=1;
                    }elseif(($total_chapter-$no)>=20 && ($total_chapter-$no)<60){//购买20章98折
                        $price=round($price*0.98);
                        $discount=0.98;
                    }elseif (($total_chapter-$no)>=60 && ($total_chapter-$no)<100){//购买60章9折
                        $price=round($price*0.9);
                        $discount=0.9;
                    }elseif (($total_chapter-$no)>100){//购买100章以上8折
                        $price=round($price*0.8);
                        $discount=0.8;
                    }


                    //判断用户账户是否有书券
                    //定义抵扣书券为0
                    $voucher=0;
                    if($user->voucher>0){
                        //计算书券抵扣金额(最多只能抵扣25%)
                        $deduction=round($price*0.25);//最多抵扣的金额

                        if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                            $voucher=$deduction;

                        }else{//如果账户书券小于最多抵扣书券,减去账户所有书券

                            $voucher=round($user->voucher);
                        }
                    }

                    //最终价格
                    $DiscountedPrice=round($price-$voucher);
                    //var_dump($DiscountedPrice);exit;
                    if($ticket!=$DiscountedPrice || $BookCoupons!=$voucher){
                        $relust['msg']='价格计算有误';
                        return $relust;
                    }

                    //计算用户账户余额加书券是否大于图书价格
                    if($user->ticket<$DiscountedPrice){
                        $relust['code']=401;
                        $relust['msg']='余额不足';
                        return $relust;
                    }else{

                        //用户消费记录,用户已购书记录,账户扣减
                        $consume=new Consume();
                        $consume->user_id=$user_id;
                        $consume->book_id=$book_id;
                        $consume->consumption=$RealPrice;
                        $consume->deductible=$voucher;
                        $consume->discount=$discount;
                        $consume->deduction=$DiscountedPrice;
                        $consume->content=$str;
                        $consume->create_time=time();

                        $transaction=\Yii::$app->db->beginTransaction();//开启事务
                        try{
                            $consume->save();

                            ////////////记录用户购买书开始////////////////
                            if($purchased){

                                //用户已购买该书
                                $purchased->user_id=$user_id;
                                $purchased->book_id=$book_id;
                                $purchased->chapter_no=$purchased->chapter_no.'|'.$str;
                                $purchased->save();
                            }else{
                                //用户还没购买该书

                                $purchased=new Purchased();
                                $purchased->user_id=$user_id;
                                $purchased->book_id=$book_id;
                                $purchased->chapter_no=$str;
                                $purchased->save();
                            }
                            $user->ticket=$user->ticket-$ticket;
                            $user->voucher=$user->voucher-$BookCoupons;
                            $user->save();
                            $transaction->commit();
                            $relust['code']=200;
                            $relust['msg']='购买成功';


                        }catch ( Exception $e){
                            //事务回滚
                            $transaction->rollBack();
                        }

                    }


                }else{
                    //根据传入的章节数量计算价格
                    if($purchased){//用户已经购买了该章节
                        //将已购章节字符串分割成数组
                        $chapter_no=explode('|',$purchased->chapter_no);
                        //删除数组空元素
                        $chapter_no=array_filter($chapter_no);//删除数组空元素
                        $QuantityPurchased=count($chapter_no);//统计已购买章节
                        //计算免费章节数和已购买章节数
                        $num=($book->no-1)+$QuantityPurchased;
                        //剩余未购买的章节数
                        $Surplus=$total_chapter-$num;
                        //最大章节(也就是最后购买章节)
                        $new_chapter_no=max($chapter_no);
                        //判断剩余章节是否大于用户选择购买章节
                        if($chapter_number>$Surplus){
                            $chapter_number=$Surplus;

                        }

                        //循环获取章节字数
                        for ($i=$new_chapter_no;$i<($chapter_number+$new_chapter_no);$i++){
                            $word_count+=$records->content->data[$i]->word_count;
                            $str.=$records->content->data[$i]->sortid.'|';
                        }

                    }else{
                        //用户没有购买过该书,默认从收费章节开始购买
                        $charge=$book->no-1;//在数组中开始收费章节
                        //剩余未购买章节数
                        $Surplus=$total_chapter-$charge;
                        //循环计算购买章节字数
                        for ($i=$charge;$i<($chapter_number+$charge);$i++){
                            $word_count+=$records->content->data[$i]->word_count;
                            $str.=$records->content->data[$i]->sortid.'|';
                        }
                    }

                    //计算购书价格
                    $price=round($book->price*($word_count/1000));

                    $RealPrice=$price;//实际价格
                    $discount='';//定义折扣后价格

                    //图书折扣
                    //图书折扣
                    if($Surplus>=$chapter_number){
                        if($chapter_number<20){
                            $price=round($price*1);
                            $discount=1;
                        }elseif($chapter_number>=20 && $chapter_number<60){//购买20章98折
                            $price=round($price*0.98);
                            $discount=0.98;
                        }elseif ($chapter_number>=60 && $chapter_number<100){//购买60章9折
                            $price=round($price*0.9);
                            $discount=0.9;
                        }elseif ($chapter_number>=100){//购买100章以上
                            $price=round($price*0.85);
                            $discount=0.85;
                        }
                    }else{
                        if($Surplus<20){
                            $price=round($price*1);
                            $discount=1;
                        }elseif($Surplus>=20 && $Surplus<60){//购买20章98折
                            $price=round($price*0.98);
                            $discount=0.98;
                        }elseif ($Surplus>=60 && $Surplus<100){//购买60章9折
                            $price=round($price*0.9);
                            $discount=0.9;
                        }elseif ($Surplus>=100){//购买100章以上
                            $price=round($price*0.85);
                            $discount=0.85;
                        }
                    }

                    //定义抵扣书券为0
                    $voucher=0;
                    if($user->voucher>0){

                        //计算书券抵扣金额(最多只能抵扣25%)
                        $deduction=round($price*0.25);//最多抵扣的金额

                        if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                            $voucher=$deduction;

                        }else{//如果账户书券小于最多抵扣书券,减去账户所有书券
                            $voucher=round($user->voucher);

                        }
                    }
                    //最终价格
                    $DiscountedPrice=round($price-$voucher);
                    if($ticket!=$DiscountedPrice || $BookCoupons!=$voucher){
                        $relust['msg']='价格计算有误';
                        return $relust;
                    }

                    //计算用户账户余额加书券是否大于图书价格
                    if($user->ticket< $DiscountedPrice){
                        $relust['code']=401;
                        $relust['msg']='余额不足';
                        return $relust;
                    }else{

                        //用户消费记录,用户已购书记录,账户扣减
                        $consume=new Consume();
                        $consume->user_id=$user_id;
                        $consume->book_id=$book_id;
                        $consume->consumption=$RealPrice;
                        $consume->deductible=$voucher;
                        $consume->discount=$discount;
                        $consume->deduction=$DiscountedPrice;
                        $consume->content=$str;
                        $consume->create_time=time();

                        $transaction=\Yii::$app->db->beginTransaction();//开启事务
                        try{
                            $consume->save();

                            ////////////记录用户购买书开始////////////////
                            if($purchased){

                                //用户已购买该书
                                $purchased->user_id=$user_id;
                                $purchased->book_id=$book_id;
                                $purchased->chapter_no=$purchased->chapter_no.'|'.$str;
                                $purchased->save();
                            }else{
                                //用户还没购买该书

                                $purchased=new Purchased();
                                $purchased->user_id=$user_id;
                                $purchased->book_id=$book_id;
                                $purchased->chapter_no=$str;
                                $purchased->save();
                            }
                            $user->ticket=$user->ticket-$ticket;
                            $user->voucher=$user->voucher-$BookCoupons;
                            $user->save();
                            $transaction->commit();
                            $relust['code']=200;
                            $relust['msg']='购买成功';


                        }catch ( Exception $e){
                            //事务回滚
                            $transaction->rollBack();
                        }

                    }
                }



           // }


        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //单章购买
    public function actionSingleEmption(){
        $relust=[
            'code'=>400,
            'msg'=>'',
        ];
        if(\Yii::$app->request->isPost){
            $obj = new Verification();
            $res = $obj->check();
            //if($res){
               // $result['msg']= $res;
           // }else{
                //接收客户端参数
                $book_id=\Yii::$app->request->post('book_id');//图书id
                $chapter_id=\Yii::$app->request->post('chapter_id');//章节id
                $user_id=\Yii::$app->request->post('user_id');//用户id
                $ticket=\Yii::$app->request->post('ticket');//消费阅票数量
                $BookCoupons=\Yii::$app->request->post('voucher');//消费书券数量

                //检测是否传入指定参数
                if(!isset($book_id) || !isset($chapter_id) || !isset($user_id) || !isset($ticket) || !isset($BookCoupons)){
                    $relust['msg']='请传入指定参数';
                    return $relust;
                }

                //查询该书价格以及出处
                $book=Book::findOne(['id'=>$book_id]);
                //查询用户账户
                $user=User::findOne(['id'=>$user_id]);
                if(!$user){
                    $relust['msg']='没有该用户';
                    return $relust;
                }
                //根据版权章节id查询出章节字数
                //请求地址
                $postUrl = 'http://partner.chuangbie.com/partner/chapterinfo';
                $curlPost =[
                    'partner_id'=>2130,
                    'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                    'book_id'=>$book->copyright_book_id,
                    'chapter_id'=>$chapter_id,
                ];

                $post=new PostRequest();
                $record=json_decode($post->request_post($postUrl,$curlPost));

                $word_count=$record->content->data->word_count;//购买章节字数
                $chapter_no=$record->content->data->sortid;
                //var_dump($chapter_no);exit;
                //计算购书价格
                $price=round($book->price*($word_count/1000));
                $RealPrice=$price;//实际价格



            //判断用户账户是否有书券
            //定义抵扣书券为0
            $voucher=0;
            if($user->voucher>0){
                //计算书券抵扣金额(最多只能抵扣25%)
                $deduction=round($price*0.25);//最多抵扣的金额
                if($user->voucher>$deduction){//如果账户书券大于最多抵扣书券,减去抵扣书券
                    $voucher=$deduction;
                }else{//如果账户书券小于最多抵扣书券,减去账户所有书券
                    $voucher=$user->voucher;
                }
            }
            //最终价格
            $DiscountedPrice=round($price-$voucher);
            if($ticket!=$DiscountedPrice || $BookCoupons!=$voucher){
                $relust['msg']='价格计算有误';
                return $relust;
            }

            //计算用户账户余额加书券是否大于图书价格
            if($user->ticket<$DiscountedPrice){
                $relust['code']=401;
                $relust['msg']='账户余额不足';
                return $relust;
            }else{
                //用户消费记录,用户已购书记录,账户扣减
                $consume=new Consume();
                $consume->user_id=$user_id;
                $consume->book_id=$book_id;
                $consume->consumption=$RealPrice;
                $consume->deductible=$voucher;
                $consume->discount=1;
                $consume->deduction=$DiscountedPrice;
                $consume->content=$chapter_no;
                $consume->create_time=time();
                $transaction=\Yii::$app->db->beginTransaction();//开启事务
                try{
                    $consume->save();

                    ////////////记录用户购买书开始////////////////
                    $purchased=Purchased::find()->where(['user_id'=>$user_id,'book_id'=>$book_id])->one();
                    if($purchased){
                        //用户已购买该书
                        $purchased->user_id=$user_id;
                        $purchased->book_id=$book_id;
                        $purchased->chapter_no=$purchased->chapter_no.'|'.$chapter_no;
                        $purchased->save();
                    }else{
                        //用户还没购买该书

                        $purchased=new Purchased();
                        $purchased->user_id=$user_id;
                        $purchased->book_id=$book_id;
                        $purchased->chapter_no=$chapter_no;
                        $purchased->save();
                    }
                    $user->ticket=$user->ticket-$ticket;
                    $user->voucher=$user->voucher-$BookCoupons;
                    $user->save();
                    $transaction->commit();
                    $relust['code']=200;
                    $relust['msg']='购买成功';


                }catch ( Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                }

            }

           // }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }

    //多章购买消费计算
    /*public function actionMultiCalculation(){
        $relust=[
          'code'=>400,
           'msg'=>''
        ];
        if(\Yii::$app->request->isPost){
            $obj = new Verification();
            $res = $obj->check();
             //if($res){
                 //$result['msg']= $res;
            // }else{
                 //接收参数
                 $book_id=\Yii::$app->request->post('book_id');//书id
                 $user_id=\Yii::$app->request->post('user_id');//用户id
                 //判断是否传入指定参数
                 if(empty($book_id) || empty($user_id)){
                     $relust['msg']='未传入指定参数';
                     return $relust;
                 }
                 //查询该书基本信息
                 $book=Book::findOne(['id'=>$book_id]);
                 //查询用户基本信息
                 $user=User::findOne(['id'=>$user_id]);
                 //请求版权方书本信息接口
                 $postUrl = 'http://partner.chuangbie.com/partner/bookinfo';
                 $curlPost =[
                     'partner_id'=>2130,
                     'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                     'book_id'=>$book->copyright_book_id,
                 ];
                 $post=new PostRequest();
                 $record=json_decode($post->request_post($postUrl,$curlPost));
                 $word_count=$record->content->data->word_count;//该书总字数

                //请求版权方图书章节列表
                $postUrl2 = 'http://partner.chuangbie.com/partner/chapterlist';
                $curlPost2 =[
                    'partner_id'=>2130,
                    'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b',
                    'book_id'=>$book->copyright_book_id,
                ];
                $post2=new PostRequest();
                $record2=json_decode($post2->request_post($postUrl2,$curlPost2));
                $chapter_number=count($record2->content->data);//该书总章节数
                //var_dump($chapter_number);exit;

                //每章价格
                $price=round(($word_count/1000*$book->price)/$chapter_number);
                 //查询用户已购章节
                 $purchased=Purchased::find()->where(['user_id'=>$user_id])->one();
                 $RealPrice=0;//实际价格
                 $discount=0;//折扣
                 if($purchased){
                     //已购买该书
                     $chapter_no=explode('|',$purchased->chapter_no);
                     //最大章节(也就是最后购买章节)
                     $chapter_no=array_filter($chapter_no);//去除数组空元素
                     $no=count($chapter_no);//统计购买章节数量
                     $new_chapter_no=max($chapter_no);//最新章节号
                     //剩余购买的章节数
                     $RemainingChapters=$chapter_number-$no;
                     $RealPrice=round($RemainingChapters*$price);//实际价格

                     //图书折扣
                     if($RemainingChapters>=20 && $RemainingChapters<60){//购买20章98折
                         $SettlementPrice=round($RealPrice*0.98);
                         $discount=0.98;
                         //var_dump($price);exit;
                     }elseif ($RemainingChapters>=60 && $RemainingChapters<100){//购买60章9折
                         $SettlementPrice=round($RealPrice*0.9);
                         $discount=0.9;
                     }elseif ($RemainingChapters>100){//购买100章以上
                         $SettlementPrice=round($RealPrice*0.8);
                         $discount=0.8;
                     }

                 }else{
                     //没有购买该书
                     //图书折扣
                     $RealPrice=$chapter_number*$price;//实际价格
                     if($chapter_number>=20 && $chapter_number<60){//购买20章98折
                         $SettlementPrice=round($RealPrice*0.98);
                         $discount=0.98;
                         //var_dump($price);exit;
                     }elseif ($chapter_number>=60 && $chapter_number<100){//购买60章9折
                         $SettlementPrice=round($RealPrice*0.9);
                         $discount=0.9;
                     }elseif ($chapter_number>100){//购买100章以上
                         $SettlementPrice=round($RealPrice*0.8);
                         $discount=0.8;
                     }

                 }
                 $relust['code']=200;
                 $relust['msg']='获取价格信息成功';
                 $relust['data']=['RealPrice'=>$RealPrice,'price'=>$price,'discount'=>$discount,'SettlementPrice'=>$SettlementPrice,'ticket'=>$user->ticket,'voucher'=>$user->voucher,'discount'=>$discount,'new_chapter_no'=>isset($new_chapter_no)?$new_chapter_no:''];


            // }
        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }*/



}