<?php
//用户接口
namespace frontend\controllers;
use backend\models\Author;
use backend\models\Book;
use backend\models\Category;
use backend\models\Purchased;
use backend\models\Reading;
use backend\models\User;
use backend\models\UserDetails;
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
                $request=\Yii::$app->request;
                $data=$request->post();
                $tel=$data['tel'];
                $captcha=$data['captcha'];
                $redis=new \Redis();
                $redis->connect('127.0.0.1');
                $phone=$redis->get('tel'.$tel);
                $sms=$redis->get('captcha');
                $time=$redis->get('time'.$tel);
                if(!$phone){
                    $result['msg']='请先发送验证';
                    return $result;
                }
                if($phone!=$tel && $captcha!=$sms){
                    $result['msg']='验证码与手机号不匹配';
                    return $result;
                }
                if($time&&(time()-$time>180)){
                    $result['msg']='验证码已过期';
                    return $result;
                }
                //验证电话唯一性
                $tel=User::findOne(['tel'=>$tel]);
                if($tel){
                    $result['msg']='电话已存在';
                    return $result;
                }
                //实例化User
                $User=new User();
                $User->tel=$data['tel'];
                $User->password_hash=\Yii::$app->security->generatePasswordHash($data['password']);
                if($User->validate()){

                    $User->auth_key=\Yii::$app->security->generateRandomString();
                    $User->created_at=time();
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
           //if($res){
             //  $result['msg']= $res;
           // }else{

                $tel=\Yii::$app->request->post('tel');
                $password=\Yii::$app->request->post('password');
                $User=User::findOne(['tel'=>$tel,'status'=>1]);
                if($User){
                    //查到用户
                    if(\Yii::$app->security->validatePassword($password,$User->password_hash)){
                        $model = UserDetails::findOne(['user_id' => $User->id]);
                        if($model->f_type){
                            $category_ids=explode('|',$model->f_type);//分割喜欢的类型字段为数组
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
                                $Books= Book::findBySql("SELECT id,name FROM book where id=$collect limit 5")->one();
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

                        $result['code']=200;
                        $result['msg']='登录成功';
                        $result['data']=['user_id'=>$User->id,'uid'=>$User->uid,'tel'=>$User->tel,'email'=>$User->email,
                            'status'=>$User->status,'created_at'=>$User->created_at,'birthday'=>$model->birthday,
                            'sex'=>$model->sex,'head'=>HTTP_PATH.$model->head,'time'=>$model->time,'author'=> $AuthorName,
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
          // }
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
                $model=User::findOne(['tel'=>$tel]);
                if($model){
                    $redis=new \Redis();
                    $redis->connect('127.0.0.1');
                    $phone=$redis->get('tel'.$tel);
                    $sms=$redis->get('captcha');
                    $time=$redis->get('time'.$tel);
                    if(!$phone){
                        $result['msg']='请先发送验证嘛';
                        return $result;
                    }
                    if($phone!=$tel && $captcha!=$sms){
                        $result['msg']='验证码与手机号不匹配';
                        return $result;
                    }
                    if($time&&(time()-$time>5000)){
                        $result['msg']='验证码已过期';
                        return $result;
                    }
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
            "阅酷书城", // 短信签名
            "SMS_113461555", // 短信模板编号
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
                $time=\Yii::$app->request->post('time');
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
           // if($res){
                //接口验证不通过
               // $result['msg']= $res;
            //}else{
                //接收手机端
                $user_id=\Yii::$app->request->post('user_id');//用户id
                $head=isset($_FILES['head'])?$_FILES['head']:'';//头像

                $nickname=\Yii::$app->request->post('nickname');//昵称
                $sex=\Yii::$app->request->post('sex');//性别
                $birthday=\Yii::$app->request->post('birthday');//生日
                $model=UserDetails::findOne(['user_id'=>$user_id]);
                $old_path=UPLOAD_PATH.$model->head;
                if($model){
                    //有上传头像,处理上传文件
                    if($head){
                        $name = $head['name'];
                        return  $head;
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
                        $fileName =date("HiiHsHis")  . '.'.$type;
                        $dir = $dir . "/" . $fileName;
                        //移动文件
                        move_uploaded_file($head['tmp_name'],$dir);
                        $uploadSuccessPath = date("Y").'/'.date("m").'/'.date("d").'/' . $fileName;
                        $model->head =$uploadSuccessPath;
                        unlink($old_path);//删除原文件
                    }

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
                    $model->save();
                    $result['code']=200;
                    $result['msg']='用户信息修改成功';

                }else{
                    $result['msg']='没有该用户';
                }

            //}

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
           if($res){
                //接口验证不通过
                $result['msg']= $res;
           }else{
                //实例化request
                $requset=\Yii::$app->request;
                //接收手机端传过来的数据
                $imei=$requset->post('imei');//用户手机唯一标示
                $address=$requset->post('address');//用户地域
                //通过imei判断是返回用户信息还是记录用户信息
                $UserObj=User::findOne(['imei'=>$imei]);
                if($UserObj){
                    //数据库有该用户
                    $result['code']=200;
                    $result['msg']='获取用户信息成功';
                    $result['data']=['user_id'=>$UserObj->id,'imei'=>$UserObj->imei,
                        'address'=>$UserObj->address,'uid'=>$UserObj->uid];

                }else{
                    //数据库没有该用户
                    $User=new User();
                    $User->imei=$imei;
                    $User->address=$address;
                    $User->status=1;
                    $uid=$this->getuid();
                    $res=\Yii::$app->db->createCommand("SELECT uid FROM user WHERE uid='$uid")->queryAll();
                    while ($res){
                        $uid=$this->getuid();
                        $res=\Yii::$app->db->createCommand("SELECT uid FROM user WHERE uid='$uid'")->queryAll();
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
}