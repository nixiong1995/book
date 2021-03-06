<?php
//用户接口
namespace frontend\controllers;
use backend\models\Author;
use backend\models\Book;
use backend\models\Category;
use backend\models\Consume;
use backend\models\Purchased;
use backend\models\Reading;
use backend\models\Recharge;
use backend\models\User;
use backend\models\UserDetails;
use frontend\models\Gift;
use frontend\models\SmsDemo;
use libs\Check;
use libs\Verification;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;


class UserController extends Controller {

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }


    //生成用户账号
    function getuid() {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $username = "";
        for ( $i = 0; $i < 6; $i++ )
        {
            $username .= $chars[mt_rand(0, 35)];
        }
        return strtoupper(base_convert(time() - 1420070400, 8, 36)).$username;
    }

    //用户注册
    public function actionUserRegister(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>['user_id'=>''],
        ];
        if(\Yii::$app->request->isPost){
            //验证接口
            $obj=new Verification();
            $res=$obj->check();
            if($res){
               $result['msg']= $res;
            }else{
                //接收数据
                $tel=\Yii::$app->request->post('tel');//电话
                $captcha=\Yii::$app->request->post('captcha');//验证码
                $imei=\Yii::$app->request->post('imei');//手机唯一标示
                $source=\Yii::$app->request->post('source');//来源
                $address=\Yii::$app->request->post('address');//地域
                $password=\Yii::$app->request->post('password');//地域
                $redis=new \Redis();
                $redis->connect('127.0.0.1');
                $phone=$redis->get('tel'.$tel);
                $sms=$redis->get('captcha'.$tel);
                $time=$redis->get('time'.$tel);
                /*//判断是否有imei
                if(empty($imei)){
                    $result['msg']='没有IMEI号';
                    return $result;
                }*/
                //判断是否输入密码
                if(empty($password)){
                    $result['msg']='没有密码';
                    return $result;
                }
                if(!$phone){
                    $result['msg']='验证码错误';
                    return $result;
                }
                if($captcha==null || $captcha!=$sms){
                    $result['msg']='验证码错误';
                    return $result;
                }
                if($time==null || (time()-$time>30000)){
                    $result['msg']='验证码已过期';
                    return $result;
                }
                //验证电话唯一性
                $res=User::findOne(['tel'=>$phone]);
                if($res){
                    $result['msg']='电话已存在';
                    return $result;
                }
                $ticket=0;
                $voucher=0;
                $gift=Gift::find()->where(['phone'=>$phone])->one();
                if($gift){
                    $ticket=$gift->ticket;
                    $voucher=$gift->voucher;
                }


                //根据IMEI查询记录的用户信息
               $model1=User::findOne(['imei'=>$imei]);
                if($model1){
                    if($model1->tel){
                        //该imei已绑定手机号,说明参数手机号未被注册,新建一条用户记录
                        $User=new User();
                        $uid=$this->getuid();
                        $res=\Yii::$app->db->createCommand("SELECT uid FROM user WHERE uid='$uid'")->queryOne();
                        while ($res){
                            $uid=$this->getuid();
                            $res=\Yii::$app->db->createCommand("SELECT uid FROM user WHERE uid='$uid'")->queryOne();
                        }
                        $User->uid=$uid;
                        $User->tel=$tel;
                        $User->password_hash=\Yii::$app->security->generatePasswordHash($password);
                        $User->auth_key=\Yii::$app->security->generateRandomString();
                        $User->address=$address;
                        $User->source=$source;
                        $User->created_at=time();
                        $User->imei='';
                        //$User->ticket=$ticket;
                        //$User->voucher=$voucher;
                        $User->status=1;
                        $transaction=\Yii::$app->db->beginTransaction();//开启事务
                        try{
                            $User->save();
                            //实例化UserDetails
                            $model=new UserDetails();
                            $model->user_id=$User->id;
                            if ($model->validate()) {//验证规则
                                //保存所有数据
                                $model->save();
                                $result['code']=200;
                                $result['msg']='注册成功';
                                $result['data']=['user_id'=>$model->user_id];
                            }
                            $transaction->commit();
                        }catch ( Exception $e){
                            //事务回滚
                            $transaction->rollBack();
                        }

                    }else{
                        //数据库已有用户信息,完善用户信息
                        $model1->tel=$tel;
                        //$User->ticket=$ticket;
                        //$User->voucher=$voucher;
                        if($address){
                            $model1->address=$address;
                        }
                        if($source && !$model1->source){
                            $model1->source=$source;
                        }
                        $model1->password_hash=\Yii::$app->security->generatePasswordHash($password);
                        $model1->auth_key=\Yii::$app->security->generateRandomString();
                        $model1->save();
                        $result['code']=200;
                        $result['msg']='注册成功';
                        $result['data']=['user_id'=>$model1->id];
                    }

                }else{

                    //没有imei,重新生成一条新数据
                    $User=new User();
                    $uid=$this->getuid();
                    $res=\Yii::$app->db->createCommand("SELECT uid FROM user WHERE uid='$uid'")->queryOne();
                    while ($res){
                        $uid=$this->getuid();
                        $res=\Yii::$app->db->createCommand("SELECT uid FROM user WHERE uid='$uid'")->queryOne();
                    }
                    $User->uid=$uid;
                    $User->tel=$tel;
                    $User->password_hash=\Yii::$app->security->generatePasswordHash($password);
                    $User->auth_key=\Yii::$app->security->generateRandomString();
                    $User->address=$address;
                    $User->source=$source;
                    $User->created_at=time();
                    $User->imei=$imei;
                    //$User->ticket=$ticket;
                    //$User->voucher=$voucher;
                    $User->status=1;
                    $transaction=\Yii::$app->db->beginTransaction();//开启事务
                    try{
                        $User->save();
                        //实例化UserDetails
                        $model=new UserDetails();
                        $model->user_id=$User->id;
                        if ($model->validate()) {//验证规则
                            //保存所有数据
                            $model->save();
                            $result['code']=200;
                            $result['msg']='注册成功';
                            $result['data']=['user_id'=>$model->user_id];
                        }
                        $transaction->commit();
                    }catch ( Exception $e){
                        //事务回滚
                        $transaction->rollBack();
                    }
                }
            }
        }else{
            $result['msg']='请求方式错误';
        }
       return $result;
    }

    //用户登录
    public function actionUserLogin(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
            'data'=>['user_id'=>''],
        ];
        if(\Yii::$app->request->isPost){
            //验证接口
            $obj=new Verification();
            $res=$obj->check();
            if($res){
              $result['msg']= $res;
            }else{

                $tel=\Yii::$app->request->post('tel');
                $password=\Yii::$app->request->post('password');
                $address=\Yii::$app->request->post('address');
                $User=User::findOne(['tel'=>$tel,'status'=>1]);
                if($User){
                    //查到用户
                    if(\Yii::$app->security->validatePassword($password,$User->password_hash)){
                        //如果address不为空,更新地址
                        if($address){
                            $User->address=$address;
                            $User->save();
                        }

                        $model = UserDetails::findOne(['user_id' => $User->id]);
                        if($model->f_type){
                            $category_ids=explode('|',$model->f_type);//分割喜欢的类型字段为数组
                            $category_ids=array_filter($category_ids);//删除数组中空元素
                            //通过分类id遍历查询喜欢的类型
                            $molds=[];//定义空数组装分类名
                            foreach ($category_ids as $category_id){
                                $category=Category::findBySql("SELECT id,name FROM category where id=$category_id ")->one();
                                $molds[$category->id]=$category->name;//将分类名装入数组中
                            }
                            $TypeName=implode('|',$molds);//分割数组成字符串
                        }else{
                            $TypeName=null;
                        }

                        if($model->f_author){
                            //通过作者id遍历查询作者名
                            $author_ids=explode('|',$model->f_author);//分割喜欢的作者为数组
                            $author_ids=array_filter($author_ids);//删除数组中空元素

                            $names=[];
                            foreach ($author_ids as $author_id){
                                $author=Author::findBySql("SELECT id,name FROM author where id=$author_id ")->one();
                                //var_dump($author);exit;
                                $names[$author->id]=$author['name'];
                            }
                            $AuthorName=implode('|',$names);
                        }else{
                            $AuthorName=null;
                        }

                        //查询用户已购买的书
                        $purchaseds=Purchased::find(['user_id'=>$User->id])->all();
                        if($purchaseds){
                            $bookdata=[];
                            foreach ($purchaseds as $purchased){
                                $book2=Book::findBySql("SELECT id,name FROM book where id=$purchased->book_id")->one();
                                $bookdata[$book2->id]=$book2->name;
                            }
                            $BookName2=implode('|',$bookdata);//购买的书
                            //var_dump($bookdata);exit;
                        }else{
                            $BookName2=null;
                        }

                        //遍历查询书名
                        if($model->collect){
                            $collects=explode('|',$model->collect);//分割收藏的书为数组
                            $books2=[];
                            foreach ($collects as $collect) {
                                $Books= Book::findBySql("SELECT id,name FROM book where id=$collect")->one();
                                $books2[$Books->id]= $Books->name;//将书名装入数组中
                            }
                            $BookName3=implode('|',$books2);//收藏的书
                        }else{
                            $BookName3=null;
                        }

                        //根据用户id到reading查询该用户读过的书id,再根据书id到book表查询书名
                        $book_ids = Reading::findBySql("SELECT book_id FROM reading where user_id=$User->id ORDER BY `create_time` DESC ")->all();
                        if($book_ids){
                            $books =[];//定义空数组装书名
                            //遍历查询书名
                            foreach ($book_ids as $book_id) {
                                $book= Book::findBySql("SELECT id,name FROM book where id=$book_id->book_id")->one();
                                $books[$book->id]=$book->name;//将书名装入数组中
                            }
                            $BookName=implode('|',$books);//分割数组成字符串
                        }else{
                            $BookName=null;
                        }

                        //处理头像
                        $head=null;
                        if($model->head){
                            $head=HTTP_PATH.$model->head;
                        }

                        $result['code']=200;
                        $result['msg']='登录成功';
                        $result['data']=['user_id'=>$User->id,'uid'=>$User->uid,'tel'=>$User->tel,'email'=>$User->email,
                            'status'=>$User->status,'created_at'=>$User->created_at,'birthday'=>$model->birthday,
                            'sex'=>$model->sex,'head'=>$head,'time'=>$model->time,'author'=> $AuthorName,
                            'Rbook'=>$BookName,'type'=>$TypeName,'ticket'=>$User->ticket,'voucher'=>$User->voucher,
                            'address'=>$User->address,'source'=>$User->source,'vip'=>$model->vip,'collect_book'=>$BookName3,
                            'purchased_book'=>$BookName2,'nickname'=>$model->nickname];
                    }else{
                        $result['msg']='密码错误';
                    }

                }else{
                    //未查到用户
                    $result['msg']='该手机未注册或者账号被封停状态';
                }
         }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //用户修改密码
    public function actionModifyPassword(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            //验证接口
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                //接口验证不通过
                $result['msg']= $res;
            }else{
                //接口验证通过
                //接收数据
                $user_id=\Yii::$app->request->post('user_id');
                $old_password=\Yii::$app->request->post('old_password');
                $new_password=\Yii::$app->request->post('new_password');
                //根据用户id查找到该用户
                $model=User::findOne(['id'=>$user_id]);
                if(\Yii::$app->security->validatePassword($old_password,$model->password_hash)){
                    $model->password_hash=\Yii::$app->security->generatePasswordHash($new_password);
                    if($model->save()){
                        $result['code']=200;
                        $result['msg']='修改密码成功';
                    }else{
                        $result['msg']='修改密码失败';
                    };

                }else{
                    $result['msg']='旧密码错误';
                }
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //用户找回密码
    public function actionForgotPassword(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
           if($res){
                //接口验证不通过
                $result['msg']= $res;
           }else{
                $tel=\Yii::$app->request->post('tel');
                $captcha=\Yii::$app->request->post('captcha');
                $password=\Yii::$app->request->post('password');
                //检测是否传入变量
                if(empty($tel) || empty($captcha) ||empty($password)){
                    $result['msg']='请传入指定参数';
                    return $result;
                }
                //获取短信验证码和手机,进行对比
                 $redis=new \Redis();
                 $redis->connect('127.0.0.1');
                 $phone=$redis->get('tel'.$tel);
                 $sms=$redis->get('captcha'.$tel);
                 $time=$redis->get('time'.$tel);
                 if(!$phone){
                     $result['msg']='验证码错误';
                     return $result;
                 }
                 if($captcha==null || $captcha!=$sms){
                     $result['msg']='验证码错误';
                     return $result;
                 }
                 if($time==null ||(time()-$time>300)){
                     $result['msg']='验证码已过期';
                     return $result;
                 }
                    $model=User::findOne(['tel'=>$tel]);
                    if($model){
                        $model->password_hash=\Yii::$app->security->generatePasswordHash($password);
                        $model->auth_key=\Yii::$app->security->generateRandomString();
                        if($model->save()){
                            $result['code']=200;
                            $result['msg']='找回密码成功';
                        }else{
                            $result['msg']='找回密码失败';
                        }

                    }else{
                        $result['msg']='没有该用户';
                    }
            }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //发送手机短信
    public function actionSms($tel){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
            'data'=>['tel'=>'','captcha'=>''],
        ];

        //判断当前是否能发送短信验证码
        $redis=new \Redis();
        $redis->connect('127.0.0.1');
        $time=$redis->get('time'.$tel);//上次发送短信的时间
        if($time && (time()-$time<60)){
            //不能发送短信
            $result['msg']='一分钟之内只允许发送一条短信';
            return $result;
        };
        //一天只能发送20条
        //检查上次发送短信时间是不是今天
        if(date("Ymd",$time)<date('Ymd',time())){
            $redis->set('count'.$tel,0);
        }
        $count=$redis->get('count'.$tel);
        if($count && $count>=20){
            $result['msg']='一天只能发送20条短信';
            return $result;
        }
        $captcha=rand(100000,999999);
        $redis=new \Redis();
        $redis->connect('127.0.0.1');
        $redis->set("tel".$tel,"$tel");
        $redis->set("captcha".$tel,"$captcha");
        $redis->set("time".$tel,time());//保存当前发送短信时间
        $redis->set('count'.$tel,++$count);
        $demo = new SmsDemo(
            "LTAIypgT6xAIPdMq",
            "tneztyzfbgbMVRB87TFKrBUhMv3HnM"
        );
        $response = $demo->sendSms(
            "阅cool书城", // 短信签名
            "SMS_117515881", // 短信模板编号
            "$tel", // 短信接收者
            Array(  // 短信模板中字段的值
                "code"=>$captcha,
                //"product"=>"dsd"
            )
        );
        //$data=['tel'=>$tel,'captcha'=>$captcha];
        if($response->Message=='OK'){
            $result['code']=200;
            $result['msg']='验证码发送成功';
            $result['data']=['tel'=>$tel,'captcha'=>$captcha];
            return $result ;
        }else{
            $result['msg']='验证码发送失败';
            return $result;
        }
        //print_r($response->Message);
    }

    //用户读书时间累加
    public function actionReadTime(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                //接口验证不通过
                $result['msg']= $res;
            }else{
                //接收手机端传过来的数据
                $user_id=\Yii::$app->request->post('user_id');
                $time=\Yii::$app->request->post('read_time');
                if($user_id && $time){
                    $model=UserDetails::findOne(['user_id'=>$user_id]);
                    if($model){
                        $model->time=$model->time+$time;
                        $model->save();
                        $result['code']=200;
                        $result['msg']='用户读书时间记录成功';

                    }else{
                        $result['msg']='没有该用户';
                    }

                }else{
                    $result['msg']='未传入指定参数';
                }
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //用户修改个人信息
    public function actionUpdate(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                //接口验证不通过
               $result['msg']= $res;
            }else{
                //接收手机端
                $user_id=\Yii::$app->request->post('user_id');//用户id
                $nickname=\Yii::$app->request->post('nickname');//昵称
                $sex=\Yii::$app->request->post('sex');//性别
                $birthday=\Yii::$app->request->post('birthday');//生日
                $model=UserDetails::findOne(['user_id'=>$user_id]);
                if($model){

                    //修改昵称
                    if($nickname){
                        $model->nickname=$nickname;
                    }

                    //修改性别
                    if($sex!==null){
                        $model->sex=$sex;
                    }

                    //修改生日
                    if($birthday){
                        $model->birthday=$birthday;
                    }
                    //保存修改
                    $model->save(false);
                    $result['code']=200;
                    $result['msg']='用户信息修改成功';

                }else{
                    $result['msg']='没有该用户';
                }
            }

        }else{
            $result['msg']='请求方式错误';

        }
        return $result;
    }

    //用户打开app记录或获取用户信息
    public function actionRecordUser(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
          //if($res){
                //接口验证不通过
               // $result['msg']= $res;
         //  }else{
                //实例化request
                $requset=\Yii::$app->request;
                //接收手机端传过来的数据
                $imei=$requset->post('imei');//用户手机唯一标示
                //判断是否有imei
                if(!$imei){
                    $result['msg']='请传入手机IMEI号';
                    return $result;
                }

                //参数处理
                $address=$requset->post('address');//用户地域
                $source=$requset->post('source');
                $address=isset($address)?$address:'';
                $source=isset($source)?$source:'';


                //通过imei判断是返回用户信息还是记录用户信息
                $UserObj=User::findOne(['imei'=>$imei]);
                if($UserObj){
                    //数据库有该用户
                    $model=UserDetails::findOne(['user_id'=>$UserObj->id]);
                    if($model->f_type){
                        $category_ids=explode('|',$model->f_type);//分割喜欢的类型字段为数组
                        $category_ids=array_filter($category_ids);//删除数组中空元素

                        //通过分类id遍历查询喜欢的类型
                        $molds=[];//定义空数组装分类名
                        foreach ($category_ids as $category_id){
                            $category=Category::findBySql("SELECT id,name FROM category where id=$category_id ")->one();
                            $molds[$category->id]=$category->name;//将分类名装入数组中
                        }
                        $TypeName=implode('|',$molds);//分割数组成字符串
                    }else{
                        $TypeName=null;
                    }

                    if($model->f_author){
                        //通过作者id遍历查询作者名
                        $author_ids=explode('|',$model->f_author);//分割喜欢的作者为数组
                        $author_ids=array_filter($author_ids);//删除数组中空元素

                        $names=[];
                        foreach ($author_ids as $author_id){
                            $author=Author::findBySql("SELECT id,name FROM author where id=$author_id ")->one();
                            //var_dump($author);exit;
                            $names[$author->id]=$author['name'];
                        }
                        $AuthorName=implode('|',$names);
                    }else{
                        $AuthorName=null;
                    }

                    //查询用户已购买的书
                    $purchaseds=Purchased::find(['user_id'=>$UserObj->id])->all();
                    if($purchaseds){
                        $bookdata=[];
                        foreach ($purchaseds as $purchased){
                            $book2=Book::findBySql("SELECT id,name FROM book where id=$purchased->book_id")->one();
                            $bookdata[$book2->id]=$book2->name;
                        }
                        $BookName2=implode('|',$bookdata);//购买的书
                        //var_dump($bookdata);exit;
                    }else{
                        $BookName2=null;
                    }

                    //遍历查询书名
                    if($model->collect){
                        $collects=explode('|',$model->collect);//分割收藏的书为数组
                        $books2=[];
                        foreach ($collects as $collect) {
                            $Books= Book::findBySql("SELECT id,name FROM book where id=$collect limit 5")->one();
                            $books2[$Books->id]= $Books->name;//将书名装入数组中
                        }
                        $BookName3=implode('|',$books2);//收藏的书
                    }else{
                        $BookName3=null;
                    }

                    //根据用户id到reading查询该用户读过的书id,再根据书id到book表查询书名
                    $book_ids = Reading::findBySql("SELECT book_id FROM reading where user_id=$UserObj->id ORDER BY `create_time` DESC ")->all();
                    if($book_ids){
                        $books =[];//定义空数组装书名
                        //遍历查询书名
                        foreach ($book_ids as $book_id) {
                            $book= Book::findBySql("SELECT id,name FROM book where id=$book_id->book_id")->one();
                            $books[$book->id]=$book->name;//将书名装入数组中
                        }
                        $BookName=implode('|',$books);//分割数组成字符串
                    }else{
                        $BookName=null;
                    }

                    //处理头像
                    $head=null;
                    if($model->head){
                        $head=HTTP_PATH.$model->head;
                    }

                    $result['code']=200;
                    $result['msg']='获取用户信息成功';
                    $result['data']=['user_id'=>$UserObj->id,'uid'=>$UserObj->uid,'tel'=>$UserObj->tel,'email'=>$UserObj->email,
                        'status'=>$UserObj->status,'created_at'=>$UserObj->created_at,'birthday'=>$model->birthday,
                        'sex'=>$model->sex,'head'=>$head,'time'=>$model->time,'author'=> $AuthorName,
                        'Rbook'=>$BookName,'type'=>$TypeName,'ticket'=>$UserObj->ticket,'voucher'=>$UserObj->voucher,
                        'address'=>$UserObj->address,'source'=>$UserObj->source,'vip'=>$model->vip,'collect_book'=>$BookName3,
                        'purchased_book'=>$BookName2,'nickname'=>$model->nickname];

                }else{
                    //数据库没有该用户

                    $User=new User();
                    $User->imei=$imei;
                    $User->address=$address;
                    $User->status=1;
                    $User->source=$source;
                    $User->created_at=time();
                    $uid=$this->getuid();
                    $res=\Yii::$app->db->createCommand("SELECT uid FROM user WHERE uid='$uid'")->queryOne();
                    while ($res){
                        $uid=$this->getuid();
                        $res=\Yii::$app->db->createCommand("SELECT uid FROM user WHERE uid='$uid'")->queryOne();
                    }
                    $User->uid=$uid;
                    $transaction=\Yii::$app->db->beginTransaction();//开启事务
                    try{
                        $User->save();
                        //实例化UserDetails
                        $model=new UserDetails();
                        $model->user_id=$User->id;
                        //保存所有数据
                        $model->save();

                        $result['code']=200;
                        $result['msg']='记录用户信息成功';
                        $result['data']=['user_id'=>$User->id,'uid'=>$User->uid,'tel'=>null,'email'=>null,
                            'status'=>$User->status,'created_at'=>$User->created_at,'birthday'=>null,
                            'sex'=>$model->sex,'head'=>null,'time'=>0,'author'=> null,
                            'Rbook'=>null,'type'=>null,'ticket'=>0,'voucher'=>0,
                            'address'=>$User->address,'source'=>$User->source,'vip'=>$model->vip,'collect_book'=>null,
                            'purchased_book'=>null,'nickname'=>null];
                        $transaction->commit();
                    }catch ( Exception $e){
                        //事务回滚
                        $transaction->rollBack();
                    }
                }
           //}
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //用户修改头像
    public function actionEditHead(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
          if($res){
                //接口验证不通过
                $result['msg']= $res;
          }else{
                $user_id=\Yii::$app->request->post('user_id');//用户id
                $head=isset($_FILES['head'])?$_FILES['head']:'';//头像
                $model=UserDetails::findOne(['user_id'=>$user_id]);
                if($model){
                    $old_path=$model->head;//存放旧图片路径
                    if($head){
                        $name = $head['name'];
                        $type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
                        $allow_type = array('jpg','jpeg','gif','png'); //定义允许上传的类型
                        //判断文件类型是否被允许上传
                        if(!in_array($type, $allow_type)){
                            //如果不被允许，则直接停止程序运行
                            $result['msg']='图片格式不允许';
                            return $result;
                        }
                        $dir =UPLOAD_PATH .date("Y").'/'.date("m").'/'.date("d").'/';
                        if (!is_dir($dir)) {
                            mkdir($dir,0777,true);
                        }
                        $fileName =uniqid() . rand(1, 100000)  . '.'.$type;
                        $dir = $dir . "/" . $fileName;
                        //移动文件
                        move_uploaded_file($head['tmp_name'],$dir);
                        $uploadSuccessPath = date("Y").'/'.date("m").'/'.date("d").'/' . $fileName;
                        $model->head =$uploadSuccessPath;
                        $model->save();
                        if($old_path){
                            $old_path=UPLOAD_PATH.$old_path;
                            unlink($old_path);//删除原文件
                        }
                        $result['code']=200;
                        $result['msg']='修改头像成功';
                        $result['head']=HTTP_PATH.$model->head;

                    }else{
                        $result['msg']='没有上传头像';
                    }

                }else{
                    $result['msg']='没有该用户';
                }
            }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //上传喜欢的分类
    public function actionLikeCategory(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                //接口验证不通过
                $result['msg']= $res;
           }else{
                $str=\Yii::$app->request->post('type');
                $user_id=\Yii::$app->request->post('user_id');
                if($str && $user_id){
                    $model=UserDetails::findOne(['user_id'=>$user_id]);
                    if($model){
                        $model->f_type=$str;
                        $model->save();
                        $result['code']=200;
                        $result['msg']='上传喜欢类型成功';
                    }else{
                        $result['msg']='没有该用户';
                    }
                }else{
                    $result['msg']='请传入指定参数';
                }

            }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //用户账户
    public function actionAccount(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            //if($res){
                //接口验证不通过
               // $result['msg']= $res;
            //}else{
                //接收参数
                $user_id=\Yii::$app->request->post('user_id');//用户id
                if(empty($user_id)){
                    $result['msg']='请传入指定参数';
                    return $result;
                }
                //根据用户id查询用户数据
                $user=User::findBySql("SELECT ticket,voucher FROM user WHERE id=$user_id")->one();
                if($user){
                    //返回信息
                    $result['data']=['ticket'=>$user->ticket,'voucher'=>$user->voucher];
                    $result['msg']='获取账户信息成功';
                    $result['code']=200;

                }else{
                    $result['msg']='没有该用户';
                }
           // }

        }else{
            $result['msg']='请求方式错误';

        }
        return $result;
    }

    //充值记录返回
    public function actionRecharge(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
            //接口验证不通过
             $result['msg']= $res;
            }else{
                //接收参数
                $user_id=\Yii::$app->request->post('user_id');
                if(empty($user_id)){
                    $result['msg']='没有指定参数';
                    return $result;
                }
                $recharges=Recharge::find()->where(['user_id'=>$user_id])->orderBy('create_time DESC')->all();
                if($recharges){
                    foreach ($recharges as $recharge){
                        $result['data'][]=['no'=>$recharge->no,'money'=>$recharge->money,'ticket'=>$recharge->ticket,'voucher'=>$recharge->voucher,'trade_no'=>$recharge->trade_no,'mode'=>$recharge->mode,'status'=>$recharge->status,'create_time'=>$recharge->create_time,'over_time'=>$recharge->over_time];
                        $result['msg']='获取充值记录成功';
                        $result['code']=200;
                    }

                }else{
                    $result['code']=400;
                    $result['msg']='无充值记录';
                }
            }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //消费记录返回
    public function actionConsume(){
        $result = [
            'code'=>400,//状态
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
             if($res){
            //接口验证不通过
             $result['msg']= $res;
             }else{
            //接收参数
            $user_id=\Yii::$app->request->post('user_id');
            if(empty($user_id)){
                $result['msg']='没有指定参数';
                return $result;
            }
            $consumes=Consume::findAll(['user_id'=>$user_id]);//
            $consumes=Consume::find()->where(['user_id'=>$user_id])->orderBy('create_time DESC')->all();
            if($consumes){
                foreach ($consumes as $consume){
                    $result['data'][]=
                        [
                            'book_id'=>$consume->book_id,
                            'book_name'=>$consume->book->name,
                            'consumption'=>$consume->consumption,
                            'deductible'=>$consume->deductible,
                            'discount'=>$consume->discount,
                            'deduction'=>$consume->deduction,
                            'content'=>$consume->content,
                            'create_time'=>$consume->create_time,
                            ];
                    $result['msg']='获取消费记录成功';
                    $result['code']=200;
                }

            }else{
                $result['code']=400;
                $result['msg']='无消费记录';
            }
             }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}