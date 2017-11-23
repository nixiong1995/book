<?php
namespace backend\models;
use yii\base\Model;

class RoleForm extends Model
{
    public $name;
    public $describe;
    public $premissions;
    const SCENARIO_ADD='add';
    const SCENARIO_EDIT ='edit';

    //验证规则
    public function rules()
    {
        return [
            [['name','describe'],'required'],
            ['premissions','safe'],
            ['name','validateName','on'=>self::SCENARIO_ADD],
            ['name','validateEditName','on'=>self::SCENARIO_EDIT],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name'=>'角色名称',
            'describe'=>'描述',
            'premissions'=>'权限'
        ];
    }

    //获取权限选项
    public static function getPermissionItems(){
        $permissions=\Yii::$app->authManager->getPermissions();
        $Items=[];
        foreach ($permissions as $permission){
            $Items[$permission->name]=$permission->description;
        }
        return $Items;
    }

    //验证添加角色名称唯一性
    public function validateName(){
        if(\Yii::$app->authManager->getChildren($this->name)){
            $this->addError('name','角色已存在');
        };
    }

    //验证修改角色名称的唯一性
    public function validateEditName(){
        if(\Yii::$app->request->get('name')!=$this->name){
            if(\Yii::$app->authManager->getChildren($this->name)){
                $this->addError('name','角色已存在');
            };

        }
    }

}