<?php
namespace backend\models;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Menu extends  ActiveRecord
{
    //验证规则
    public function rules()
    {
        return [
            [['name','parent_id','sort'],'required'],
            ['sort','integer'],
            ['route','safe'],
        ];
    }

    //字段中文命名
    public function attributeLabels()
    {
        return [
            'name'=>'菜单名称',
            'parent_id'=>'上级菜单',
            'route'=>'地址(路由)',
            'sort'=>'排序',
        ];
    }

    //获取路由
    public static function getPermissionItems(){
        $permissions=\Yii::$app->authManager->getPermissions();
        $Items=[];
        $option=[''=>'===请选择路由==='];
        foreach ($permissions as $permission){
            $Items[$permission->name]=$permission->name;
        }
        return array_merge($option,$Items);
    }

    //获取上级菜单名
    public static function getMenuName(){
        $rows=Menu::findAll(['parent_id'=>0]);
        $menuName=[];
        $menuName['']='===请选择上级菜单===';
        $menuName[0]='顶级菜单';
        //$option=[''=>'===请选择上级菜单==='];
       // $option=array_merge($option,$top);
        foreach ( $rows as $row){
            $menuName[$row->id]=$row->name;
        }
        return $menuName;
    }

    //获取用户菜单
    public static function getMenus(){
        //获取所有一级菜单
        $menus=Menu::find()->where(['parent_id'=>0])->orderBy('sort')->all();
        $menuItems=[];
        foreach ($menus as $menu){
            //获得一级菜单的所有子菜单
            $children=Menu::find()->where(['parent_id'=>$menu->id])->orderBy('sort')->all();
            $items=[];
            foreach ($children as $child){
                //判断当前用户是否具有改路由权限
                if(\Yii::$app->user->can($child->route)){
                    $items[]=['label'=>$child->name,'url'=>[$child->route]];
                }
            }
            if($items){
                $menuItems[]=['label'=>$menu->name,'items'=>$items];
            }

        }
        return $menuItems;

    }
}