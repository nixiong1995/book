<?php
namespace frontend\controllers;
use frontend\models\Brush;
use libs\Verification;
use yii\web\Controller;
use yii\web\Response;

//广告图点击数累加
class BrushController extends Controller{

    public $enableCsrfValidation=false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionIndex(){
        $result = [
            'code'=>400,
            'msg'=>'',//错误信息,如果有
        ];
        if(\Yii::$app->request->isPost){
            $obj=new Verification();
            $res=$obj->check();
            if($res){
                $result['msg']= $res;
            }else{
                //接收参数
                $name=\Yii::$app->request->post('name');//包名
                $advert_id=\Yii::$app->request->post('advert_id');//本地图片id

                if(empty($name)){
                    $result['msg']='请传入指定参数';
                    return $result;
                }

                $query=Brush::find()->where(['name'=>$name]);
                if($advert_id){
                    $query->andWhere(['advert_id'=>$advert_id]);
                }
                $model=$query->one();

                if($model){
                    $model->click=$model->click+1;
                    $model->save();
                }else{
                    $brush=new Brush();
                    $brush->name=$name;
                    $brush->click=1;
                    $brush->advert_id=$advert_id;
                    $brush->save();
                }
                $result['msg']='点击数记录成功';
                $result['code']=200;

            }

        }else{
            $result['msg']='请求方式错误';
        }
        return $result;
    }
}