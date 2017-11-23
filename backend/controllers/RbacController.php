<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\PermissionForm;
use yii\web\Controller;

class RbacController extends Controller
{
    //权限列表
    public function actionIndex(){
        $auth=\Yii::$app->authManager;
        $permissions=$auth->getPermissions();

        return $this->render('index',['permissions'=>$permissions]);

    }

    //权限添加
    public function actionAdd(){
        $model=new PermissionForm();
        $model->scenario=PermissionForm::SCENARIO_ADD;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $auth=\Yii::$app->authManager;
                //创建权限
                $permission=$auth->createPermission($model->name);
                $permission->description=$model->describe;
                //保存权限到数据库
                $auth->add($permission);
                \Yii::$app->session->setFlash('success','添加权限成功');
                return $this->redirect(['rbac/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //权限修改
    public function actionEdit($name){
        $auth=\Yii::$app->authManager;
        $permission=$auth->getPermission($name);
        $model=new PermissionForm();
        $model->scenario=PermissionForm::SCENARIO_EDIT;
        //$model->scenario=PermissionForm::SCENARIO_EDIT;
        $model->name=$permission->name;
        $edit_before_name=$model->name;
        $model->describe=$permission->description;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                //var_dump($edit_before_name==$model->name);exit;
/*                if($edit_before_name!=$model->name){
                    //判断有没有修改权限名称,有修改的话根据修改名称查询数据表,验证权限名是否重复
                    //var_dump($edit_before_name==$model->name);exit;
                    if(\Yii::$app->authManager->getPermission($model->name)){
                       //根据用户修改的权限名称查询数据表,存在的话不允许修改,跳转回修改页面
                        \Yii::$app->session->setFlash('error','权限已存在');
                       return $this->redirect(['rbac/edit','name'=>$model->name]);
                    }

                }*/
                $permission->name=$model->name;
                $permission->description=$model->describe;
                \Yii::$app->authManager->update($name, $permission);
                \Yii::$app->session->setFlash('success','修改成功');
                return $this->redirect(['rbac/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //权限删除
    public function actionDel(){
        $name=\Yii::$app->request->post('name');
        $auth=\Yii::$app->authManager->getPermission($name);
        $relust=\Yii::$app->authManager->remove($auth);
        if($relust){
            return 'success';
        }else{
            return 'fail';
        }
    }

    public function actionData(){
        return $this->render('data');
    }

    //验证访问权限
   public function behaviors()
    {
        return [
            'rbac'=>[
                'class'=>RbacFilter::className(),
                'except'=>['login','logout','captcha','error','s-upload'],
            ]
        ];
    }

}