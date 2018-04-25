<?php
namespace frontend\controllers;
use frontend\models\Audio;
use frontend\models\Gift;
use frontend\models\Material;
use frontend\models\Member;
use frontend\models\Photos;
use frontend\models\Praise;
use frontend\wechat_small_program\wxBizDataCrypt;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;

class ActivityController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //记录用户
    public function actionRecordMember(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            $openid=\Yii::$app->request->post('openid');
            $nickName=\Yii::$app->request->post('nickName');
            $gender=\Yii::$app->request->post('gender');
            $avatarUrl=\Yii::$app->request->post('avatarUrl');
            if(empty($nickName) ||  empty($avatarUrl) || empty($openid)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            $model=Member::find()->where(['openid'=>$openid])->one();
            if($model){
                $relust['code']=200;
                $relust['msg']='成功返回用户信息';
                $relust['member_id']=$model->id;

            }else{
                $member=new Member();
                $member->openid=$openid;
                $member->nickName=$nickName;
                $member->gender=$gender;
                $member->avatarUrl=$avatarUrl;
                $member->create_time=time();
                if($member->save()){
                    $relust['code']=200;
                    $relust['msg']='记录用户成功';
                    $relust['member_id']=$member->id;
                }else{
                    $relust['code']='记录用户失败';
                }
            }


        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //上传音频文件
    public function actionUploadAudio(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            $file=$_FILES['file'];
            $material_id=\Yii::$app->request->post('material_id');
            $member_id=\Yii::$app->request->post('member_id');
            $duration=\Yii::$app->request->post('duration');
            if(empty($file) || empty($member_id) || empty($duration) || empty($material_id)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            $name = $file['name'];
            $type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
            $allow_type = array('mp3','silk','arm','avi'); //定义允许上传的类型
            //判断文件类型是否被允许上传
            if(!in_array($type, $allow_type)){
                //如果不被允许，则直接停止程序运行
                $result['msg']='图片格式不允许';
                return $result;
            }
            $dir =\Yii::getAlias('@webroot') .'/audio/'.date("Ymd").'/';
            if (!is_dir($dir)) {
                mkdir($dir,0777,true);
            }
            $fileName ='wx_'.uniqid() . rand(1, 100000)  . '.'.$type;
            $dir = $dir . "/" . $fileName;
            //移动文件
            move_uploaded_file($file['tmp_name'],$dir);
            $uploadSuccessPath = '/audio/'.date("Ymd").'/' . $fileName;
           // $path=Audio::getUpload( $audio_id);
          //  if($path==false){
               // $relust['msg']='音频转换失败';
           // }else{
                $model=new Audio();
                $model->material_id=$material_id;
                $model->member_id=$member_id;
                $model->path=$uploadSuccessPath;
                $model->duration=$duration;
                $model->status=1;
                $model->create_time=time();
                if($model->save()){
                    $relust['code']=200;
                    $relust['msg']='存入音频成功';
                }else{
                    $relust['msg']='存入音频失败';
                }
          //  }
        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }

    //上传照片
    public function actionUploadPhoto(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            $member_id=\Yii::$app->request->post('member_id');//用户id
            $limit=\Yii::$app->request->post('limit');//翻盘限制数
            $photo=isset($_FILES['photo'])?$_FILES['photo']:'';//头像
            if(empty($member_id) || empty($limit) || empty($photo)){
                $result['msg']='未传入指定参数';
                return $result;
            }
            $count=Photos::find()->where(['member_id'=>$member_id])->count('id');
            if($count<3){
                $name = $photo['name'];
                $type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
                $allow_type = array('jpg','jpeg','gif','png'); //定义允许上传的类型
                //判断文件类型是否被允许上传
                if(!in_array($type, $allow_type)){
                    //如果不被允许，则直接停止程序运行
                    $result['msg']='图片格式不允许';
                    return $result;
                }
                $dir =\Yii::getAlias('@webroot') .'/photos/'.date("Ymd").'/';
                if (!is_dir($dir)) {
                    mkdir($dir,0777,true);
                }
                $fileName =uniqid() . rand(1, 100000)  . '.'.$type;
                $dir = $dir . "/" . $fileName;
                //移动文件
                move_uploaded_file($photo['tmp_name'],$dir);
                $uploadSuccessPath = '/photos/'.date("Ymd").'/' . $fileName;
                $model=new Photos();
                $model->member_id=$member_id;
                $model->img =$uploadSuccessPath;
                $model->limit=$limit;
                $model->create_time=time();
                if($model->save()){
                    $result['code']=200;
                    $result['msg']='上传照片成功';
                }else{
                    $result['msg']='上传照片失败';
                }
            }else{
                $result['code']=201;
                $result['msg']='照片已达到上限3张';
            }



        }else{
            $result['msg']='请求方式错误';
        }
        return $result;

    }

    //查找我的作品
    public function actionMyWorks(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收用户id
            $member_id=\Yii::$app->request->post('member_id');
            if(empty($member_id)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            //查找作品
            $audios=Audio::find()->where(['member_id'=>$member_id])->all();
            if($audios){
                $relust['code']=200;
                $relust['msg']='获取作品成功';
                foreach ($audios as $audio){
                    $relust['data'][]=[
                        'id'=>$audio->id,
                        'member_id'=>$audio->member_id,
                        'material_id'=>$audio->material_id,
                        'material_name'=>$audio->material->book_name,
                        'material_img'=>$audio->material->book_img,
                        'material_content'=>$audio->material->book_content,
                        'path'=>$audio->path,
                        'praise'=>$audio->praise,
                        'duration'=>$audio->duration,
                        'create_time'=>$audio->create_time
                        ];
                }
            }else{
                $relust['code']=404;
                $relust['msg']='暂无作品';
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //获取音频录制素材
    public function actionMaterial(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            $material=Material::find()->all();
            if($material){
                $relust['code']=200;
                $relust['msg']='获取录制素材成功';
                $relust['data']=$material;
            }else{
                $relust['msg']='无录制素材';
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;

    }

    //判断该用户一个素材是否已经录制3次
    public function actionRecordingTimes(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $member_id=\Yii::$app->request->post('member_id');
            $material_id=\Yii::$app->request->post('material_id');
            if(empty($member_id) || empty($material_id)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            $num=Audio::find()->where(['member_id'=>$member_id])->andWhere(['material_id'=>$material_id])->count('id');
            if($num>3){
                $relust['code']=400;
                $relust['msg']='该素材已达到3次录制上限,请选择其他素材录制,或者删除该素材不满意的录制';
            }else{
                $relust['code']=200;
                $relust['msg']='该素材录制未达到上限,可以继续录制';
            }

        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //用户点赞
    public function actionFabulous(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $member_id=\Yii::$app->request->post('member_id');
            $audio_id=\Yii::$app->request->post('audio_id');
            if(empty($member_id) || empty($audio_id)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            $member=Member::find()->where(['id'=>$member_id])->one();
            if($member->fabulous>0){
                $praise=new Praise();
                $praise->member_id=$member_id;
                $praise->audio_id=$audio_id;
                $praise->create_time=time();
                $transaction=\Yii::$app->db->beginTransaction();//开启事务
                try{
                    $praise->save();
                    $member->fabulous=$member->fabulous-1;
                    $member->save();
                    $audio=Audio::find()->where(['id'=>$audio_id])->one();
                    $audio->praise=$audio->praise+1;
                    $audio->save();
                    $transaction->commit();
                    $relust['code']=200;
                    $relust['msg']='点赞成功';

                }catch (Exception $e){
                    //事务回滚
                    $transaction->rollBack();
                }
            }else{
                $relust['msg']='今日点赞数已用完';
            }


        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //搜索
    public function actionSearch(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $keyword=\Yii::$app->request->post('keyword');
            if(empty($keyword)){
                $relust['msg']='未传入指定参数';
                return $relust;
            }
            if(is_numeric($keyword)){
                $audio=Audio::find()->where(['id'=>$keyword])->one();
                if($audio){
                    $relust['code']=200;
                    $relust['msg']='成功返回搜索结果';
                    $relust['data'][]=[
                        'id'=>$audio->id,
                        'member_id'=>$audio->member_id,
                        'material_id'=>$audio->material_id,
                        'material_name'=>$audio->material->book_name,
                        'material_img'=>$audio->material->book_img,
                        'material_content'=>$audio->material->book_content,
                        'path'=>$audio->path,
                        'praise'=>$audio->praise,
                        'duration'=>$audio->duration,
                        'create_time'=>$audio->create_time
                    ];
                }else{
                    $relust['code']=404;
                    $relust['msg']='未搜索到结果';
                }

            }else{
                $material_id=\Yii::$app->db->createCommand("select id ,(length(book_name)-length('$keyword')) as rn from material where book_name like '%$keyword%' order by rn")->queryScalar();
                if($material_id){
                    $audios=Audio::find()->where(['material_id'=>$material_id])->all();
                    if($audios){
                        $relust['code']=200;
                        $relust['msg']='成功返回搜索结果';
                        foreach ($audios as $audio){
                            $relust['data'][]=[
                                'id'=>$audio->id,
                                'member_id'=>$audio->member_id,
                                'material_id'=>$audio->material_id,
                                'material_name'=>$audio->material->book_name,
                                'material_img'=>$audio->material->book_img,
                                'material_content'=>$audio->material->book_content,
                                'path'=>$audio->path,
                                'praise'=>$audio->praise,
                                'duration'=>$audio->duration,
                                'create_time'=>$audio->create_time
                            ];
                        }
                    }else{
                        $relust['code']=404;
                        $relust['msg']='未搜索到结果';
                    }

                }else{
                    $relust['code']=404;
                    $relust['msg']='未搜索到结果';
                }
            }


        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //排行
    public function actionRanking(){
        $relust=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isGet){
            //接收参数
            $page=\Yii::$app->request->get('page');
            $query=Audio::find()->orderBy('praise DESC')->limit(50);
            $count=ceil($query->count()/10);
            if($page>$count){
                $relust['code']=201;
                $relust['msg']='没有更多了';
                return $relust;
            }
            $pager=new Pagination([
                'totalCount'=>$query->count(),
                'defaultPageSize'=>5,
            ]);
            $models=$query->limit($pager->limit)->offset($pager->offset)->all();
            if($models){
                $relust['code']=200;
                $relust['msg']='获取排行榜数据成功';
                foreach ($models as $model){
                    $relust['data'][]=[
                        'id'=>$model->id,
                        'member_id'=>$model->member_id,
                        'material_id'=>$model->material_id,
                        'material_name'=>$model->material->book_name,
                        'material_img'=>$model->material->book_img,
                        'material_content'=>$model->material->book_content,
                        'path'=>$model->path,
                        'praise'=>$model->praise,
                        'duration'=>$model->duration,
                        'create_time'=>$model->create_time
                    ];

                }

            }else{
                $relust['code']=404;
                $relust['msg']='暂无数据';
            }


        }else{
            $relust['msg']='请求方式错误';
        }
        return $relust;
    }

    //翻牌
    public function actionSelectPhoto(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $author_id=\Yii::$app->request->post('author_id');
            if(empty($author_id)){
                $result['msg']='未传入指定参数';
                return $result;
            }
            //查询作者照片
            $photos=Photos::find()->where(['member_id'=>$author_id])->andWhere(['status'=>1])->orderBy('limit DESC')->all();
            if(!$photos){
                $result['code']=201;
                $result['msg']='暂无照片';
                return $result;
            }

            //查询作者赞数
            $praise=Audio::find()->where(['member_id'=>$author_id])->sum('praise');
            $praise=$praise?$praise:0;
            $result['code']=200;
            $result['msg']='成功返回数据';
            foreach ($photos as $photo){
                $result['data'][]=[
                    'id'=>$photo->id,
                    'member_id'=>$photo->member_id,
                    'img'=>$photo->img,
                    'limit'=>$photo->limit,
                    'status'=>$photo->status,
                    'create_time'=>$photo->create_time,
                    'praise'=>$praise,

                    ];
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //删除录制
    public function actionDelAudio(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $audio_id=\Yii::$app->request->post('audio_id');
            if(empty($audio_id)){
                $result['msg']='未传入指定参数';
                return $result;
            }
            $audio=Audio::find()->where(['id'=>$audio_id])->one();
            if($audio_id){
                $path=$audio->path;
                if($audio->delete()){
                    $path=\Yii::getAlias('@webroot').$path;
                    unlink($path);
                    $result['code']=200;
                    $result['msg']='删除音频成功';
                }else{
                    $result['msg']='删除音频失败';
                }
            }else{
                $result['code']=201;
                $result['msg']='未找到该音频';
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //删除照片
    public function actionDelPhoto(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            $photo_id=\Yii::$app->request->post('photo_id');
            if(empty($photo_id)){
                $result['msg']='未传入指定参数';
                return $result;
            }
            $photo=Photos::find()->where(['id'=>$photo_id])->one();
            if($photo){
                $path=$photo->img;
                if($photo->delete()){
                    $path=\Yii::getAlias('@webroot').$path;
                    unlink($path);
                    $result['code']=200;
                    $result['msg']='删除照片成功';
                }else{
                    $result['msg']='删除照片失败';
                }
            }else{
                $result['code']=201;
                $result['msg']='未找到该照片';
            }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //赠送阅票或者书券
    public function actionGift(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $phone=\Yii::$app->request->post('phone');
            $ticket=\Yii::$app->request->post('ticket');
            $voucher=\Yii::$app->request->post('voucher');
            if(empty($phone) || (empty($ticket) && empty($voucher))){
                $result['msg']='未传入指定参数';
                return $result;
            }
            $model=Gift::find()->where(['phone'=>$phone])->one();
            if($model){
                //已有赠送记录
                if($ticket){
                    $model->ticket=$model->ticket+$ticket;
                }else{
                    $model->voucher=$model->voucher+$voucher;
                }
                if($model->save()){
                    $result['code']=200;
                    $result['msg']='记录成功';
                }else{
                    $result['msg']='记录失败';
                }
            }else{
                //未有赠送记录
                $model=new Gift();
                $model->phone=$phone;
                if($ticket){
                    $model->ticket=$ticket;
                }else{
                    $model->voucher=$voucher;
                }
                if($model->save()){
                    $result['code']=200;
                    $result['msg']='记录成功';
                }else{
                    $result['msg']='记录失败';
                }
            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    //音频详情
    public function actionAudioDetails(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $audio_id=\Yii::$app->request->post('audio_id');
            $member_id=\Yii::$app->request->post('member_id');
            if(empty($audio_id) || empty($member_id)){
                $result['msg']='未传入指定参数';
                return $result;
            }
            $member=Member::find()->where(['id'=>$member_id])->one();
            $audio=Audio::find()->where(['id'=>$audio_id])->one();
            if($member_id && $audio){
                $result['code']=200;
                $result['msg']='成功返回信息';
                $result['data']['member']=[
                    'member_id'=>$member->id,
                    'nickName'=>$member->nickName,
                    'gender'=>$member->gender,
                    'avatarUrl'=>$member->avatarUrl,
                    'fabulous'=>$member->fabulous,
                    'create_time'=>$member->create_time,
                ];
                $result['data']['audio']=[
                    'id'=>$audio->id,
                    'member_id'=>$audio->member_id,
                    'material_id'=>$audio->material_id,
                    'material_name'=>$audio->material->book_name,
                    'material_img'=>$audio->material->book_img,
                    'material_content'=>$audio->material->book_content,
                    'path'=>$audio->path,
                    'praise'=>$audio->praise,
                    'duration'=>$audio->duration,
                    'create_time'=>$audio->create_time
                ];

            }else{
                $result['code']=404;
                $result['msg']='未找到用户或者作品';
            }

        }else{
            $result['msg']='请求方式错误';

        }
        return $result;
    }

    //点赞记录
    public function actionPraiseRecord(){
        $result=[
            'code'=>400,
            'msg'=>'请求失败',
        ];
        if(\Yii::$app->request->isPost){
            //接收参数
            $member_id=\Yii::$app->request->post('member_id');
            if(empty($member_id)){
                $result['msg']='未传入指定参数';
                return $result;
            }
            $audio_Ids=Praise::find()->select('audio_id')->where(['member_id'=>$member_id])->column();
            if($audio_Ids){
                $audios=Audio::find()->where(['id'=>$audio_Ids])->orderBy('praise DESC')->all();
                if($audios){
                    $result['code']=200;
                    $result['msg']='成功返回信息';
                    foreach ($audios as $audio){
                        $result['data'][]=[
                            'id'=>$audio->id,
                            'member_id'=>$audio->member_id,
                            'material_id'=>$audio->material_id,
                            'material_name'=>$audio->material->book_name,
                            'material_img'=>$audio->material->book_img,
                            'material_content'=>$audio->material->book_content,
                            'path'=>$audio->path,
                            'praise'=>$audio->praise,
                            'duration'=>$audio->duration,
                            'create_time'=>$audio->create_time,
                            'nickName'=>$audio->member->nickName,
                        ];
                    }

                }else{
                    $result['code']=201;
                    $result['msg']='没有音频';
                }

            }else{
                $result['code']=404;
                $result['msg']='你还未给作品点过赞';
            }
        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }

    // 获取微信用户信息
    public function actionGetWxLogin()
    {
        // require_once ROOTPATH . "./PHP/wxBizDataCrypt.php";
        $request=\Yii::$app->request;
        $code   =   $request->get('code');
        $encryptedData   =  $request->get('encryptedData');
        $iv   =   $request->get('iv');
        $appid  =  "wx922ebe9f5d8ba438" ;
        $secret =   "3b46cfaf6fdaa7698f128cb25f33c301";

        $URL = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";

        $apiData=file_get_contents($URL);
        // var_dump($code,'wwwwwwww',$apiData['errscode']);
        //     $ch = curl_init();
        // 　　curl_setopt($ch, CURLOPT_URL, $URL);
        // 　　curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 　　curl_setopt($ch, CURLOPT_HEADER, 0);
        // 　　$output = curl_exec($ch);
        // 　　curl_close($ch)

        if(!isset($apiData['errcode'])){
            $sessionKey = json_decode($apiData)->session_key;
            $userifo = new \frontend\smallprogram\WxBizDataCrypt($appid, $sessionKey);
            $errCode = $userifo->decryptData($encryptedData, $iv, $data );

            if ($errCode == 0) {
                return ($data . "\n");
            } else {
                return false;
            }
        }
    }


}