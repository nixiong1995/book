<?php
namespace backend\models;
use yii\base\Model;

class PermissionForm extends Model
{
    public $name;
    public $describe;
    const SCENARIO_ADD='add';
    const SCENARIO_EDIT ='edit';

    public function rules()
    {
        return [
            [['name','describe'],'required'],
            ['name','validateName','on'=>self::SCENARIO_ADD],
            ['name','validateEditName','on'=>self::SCENARIO_EDIT],
        ];
    }
    //验证添加权限名称唯一性
    public function validateName(){
        if(\Yii::$app->authManager->getPermission($this->name)){
           $this->addError('name','权限已存在');
        };
    }

    //验证修改后权限名唯一性
    public function validateEditName(){
        if(\Yii::$app->request->get('name')!=$this->name){
            if(\Yii::$app->authManager->getPermission($this->name)){
                $this->addError('name','权限已存在');
            };
        }
    }

    //验证是否修改权限名称
  /*  public function editName(){
        if(\Yii::$app->authManager->getPermission($this->name)){
            $this->addError('name','权限已存在');
        }
    }*/


    public function attributeLabels()
    {
        return [
            'name'=>'权限名称(路由)',
            'describe'=>'描述',
        ];
    }
}