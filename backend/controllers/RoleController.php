<?php
namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\RoleForm;
use yii\web\Controller;

class RoleController extends Controller
{
    //查询角色
    public function actionIndex(){
        $auth=\Yii::$app->authManager;
        $roles=$auth->getRoles();
        return $this->render('index',['roles'=>$roles]);

    }

    //添加角色
    public function actionAdd(){
        $model=new RoleForm();
        $model->scenario=RoleForm::SCENARIO_ADD;
        $request=\Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                //保存角色
                $auth=\Yii::$app->authManager;
                //创建新角色
                $role=$auth->createRole($model->name);
                $role->description=$model->describe;
                //保存到数据库
                $auth->add($role);
                //给角色分配权限
                if($model->premissions){
                    foreach ($model->premissions as $premissionName){
                        $permission=\Yii::$app->authManager->getPermission($premissionName);
                        $auth->addChild($role,$permission);
                    }
                }
                return $this->redirect(['role/index']);

            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //修改角色
    public function actionEdit($name){
        $auth=\Yii::$app->authManager;
        $permission=$auth->getPermissionsByRole($name);//查找权限,回显
        $role=$auth->getRole($name);//查询角色
        //var_dump($permission);exit;
        $model=new RoleForm();
        $model->scenario=RoleForm::SCENARIO_EDIT;
        //赋值回显
        $model->name=$role->name;
        $model->describe=$role->description;
        $model->premissions=array_keys( $permission);
        $request=\Yii::$app->request;

        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                //var_dump($model);exit;
                //创建新角色
                $role=$auth->getRole($name);
                $role->name=$model->name;
                $role->description=$model->describe;
                //保存到数据库
                $auth->update($name,$role);
                //var_dump($model->premissions);exit;
                //先移除所有权限
                //var_dump($role);exit;
                $auth->removeChildren($role);
                //给角色修改权限
                if($model->premissions){
                    foreach ($model->premissions as $premissionName){
                        $permission=\Yii::$app->authManager->getPermission($premissionName);
                        //var_dump( $permission);exit;
                        $auth->addChild($role,$permission);
                    }
                }
                return $this->redirect(['role/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //删除角色
    public function actionDel(){
        $name=\Yii::$app->request->post('name');
        $auth=\Yii::$app->authManager;
        $role=$auth->getRole($name);
        $relust=$auth->remove($role);
        if($relust){
            return 'success';
        }else{
            return 'fail';
        }
    }

    //验证访问权限
    public function behaviors()
    {
        return [
            'rbac'=>[
                'class'=>RbacFilter::className(),
                'except'=>['login','logout','captcha','error'],
            ]
        ];
    }
}