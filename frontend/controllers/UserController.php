<?php
//用户接口
namespace frontend\controllers;
use backend\models\Author;
use backend\models\Book;
use backend\models\Category;
use backend\models\Reading;
use backend\models\User;
use backend\models\UserDetails;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

class UserController extends Controller {

    public $enableCsrfValidation=false;
    public $token = 'yueku';

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //验证接口
    public function check(){
        if (\Yii::$app->request->isPost){
            $data = \Yii::$app->request->post();
        }else{
            $data = \Yii::$app->request->get();
        }
        //时间戳验证
        $time = isset($data['time'])?$data['time']:0;
        if($time){
            //请求有效期是1分钟
            if(time()-$time>4000 || $time > time()){
                $error = '请求已过期';
                return $error;
            }
        }else{
            $error='缺少参数';
            return $error;
        }
        //验证签名
        $sign = isset($data['sign'])?$data['sign']:'';
        if($sign){
            unset($data['sign']);
            ksort($data);
            $str = http_build_query($data);
            $s = strtoupper(md5($this->token.$str));
            if($sign == $s){
            }else{
                $error='签名错误';
                return $error;
            }

        }else{
            $error='缺少参数';
            return $error;
        }
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
        ];
        if(\Yii::$app->request->isPost){
            //验证接口
            $res=$this->check();
            if($res){
                $result['msg']= $res;
            }else{
                //接收数据
                $request=\Yii::$app->request;
                $data=$request->post();
                $tel=$data['tel'];
                $email=$data['password'];
                $tel=$data['tel'];
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
                    $uid=$this->getuid();
                    $res=User::findOne(['uid'=>$uid]);
                    while ($res){
                        $uid=$this->getuid();
                    }
                    $User->uid=$uid;
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
            'data'=>[],
        ];
        if(\Yii::$app->request->isPost){
            //验证接口
            $res=$this->check();
            if($res){
                $result['msg']= $res;
            }else{

                $tel=\Yii::$app->request->post('tel');
                $password=\Yii::$app->request->post('password');
                $User=User::findOne(['tel'=>$tel]);
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
                                $names[$author['id']]=$author['name'];
                            }
                            $AuthorName=implode('|',$names);
                        }else{
                            $AuthorName=null;
                        }

                        //根据用户id到reading查询该用户读过的书id,再根据书id到book表查询书名
                        $book_ids = Reading::findBySql("SELECT book_id FROM reading where user_id=$User->id ORDER BY `create_time` DESC ")->all();
                        $books =[];//定义空数组装书名
                        //遍历查询书名
                        foreach ($book_ids as $book_id) {
                            $book= Book::findBySql("SELECT id,name FROM book where id=$book_id->book_id")->one();
                            $books[$book->id]=$book->name;//将书名装入数组中
                        }
                        $BookName=implode('|',$books);//分割数组成字符串
                        $result['code']=200;
                        $result['msg']='登录成功';
                        $result['data']=['uid'=>$User->uid,'tel'=>$User->tel,'email'=>$User->email,
                            'status'=>$User->status,'created_at'=>$User->created_at,'birthday'=>$model->birthday,
                            'sex'=>$model->sex,'head'=>$model->head,'time'=>$model->time,'author'=> $AuthorName,
                            'Rbook'=>$BookName,'type'=>$TypeName,'ticket'=>$model->ticket,'voucher'=>$model->voucher];
                    }else{
                        $result['msg']='密码错误';
                    }

                }else{
                    //未查到用户
                    $result['msg']='该手机未注册';
                }
            }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    public function actionSign(){
        //var_dump(time());exit;
        $p = ['tel'=>13895512039,'time'=>1511010420,'password'=>123456];
        //1.对key做升序排列 //['a'=>'','b'=>'','c'=>'','time'=>'']
        ksort($p);
        //2. 将参数拼接成字符串 a=4&b=123&c=77&time=12312312
        $s = http_build_query($p);
        //3 将token拼接到字符串前面.然后做md5运算,将结果转换成大写
        $sign = strtoupper(md5($this->token.$s));
        var_dump($sign);
    }


}