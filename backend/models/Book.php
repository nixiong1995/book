<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Book extends ActiveRecord{

    public $file;
    public $author_name;
    const SCENARIO_ADD ='add';

    public function rules()
    {
        return [
            [['name','is_free','intro','category_id','no','type','from'],'required'],
            ['file','required','on'=>self::SCENARIO_ADD],
            ['name', 'unique','message' => '该书已存在.'],
            [['size','clicks','score'],'number'],
            ['file', 'file', 'extensions' => ['png', 'jpg', 'gif']],
            ['type', 'in', 'range' => ['txt', 'epub'],'message'=>'只能输入txt,epub文本类型'],
            [['no','clicks'],'integer'],
            ['score', 'in', 'range' => [1, 2, 3,4,5,6,7,8,9,10],'message'=>'只能输入1-10的整数'],
            [['ascription','author_name'],'safe'],

        ];
    }

    public function attributeLabels()
    {
        return [
            'name'=>'书名',
            'author_name'=>'作者姓名',
            'is_free'=>'是否免费',
            'file'=>'书封面',
            'category_id'=>'所属分类',
            'clicks'=>'观看数',
            'no'=>'多少章节开始收费(整本书不收费请输入0)',
            'score'=>'评分',
            'intro'=>'书简介',
            'type'=>'文本类型',
            'from'=>'书来自于(你的选择关系重大,请核对清楚再进行选择)',
            'ascription'=>'书归属于(你的选择关系重大,请核对清楚再进行选择)',
            'author_intro'=>'作者简介',
            'file2'=>'作者图片',
        ];
    }

    //搜索获取分类名
    public static function getCategoryName(){
        $rows=Category::findAll(['status'=>1]);
        $CategoryName=[];
        $CategoryName[0]='请选择分类';
        foreach ( $rows as $row){
            $CategoryName[$row->id]=$row->name;
        }
        return $CategoryName;
    }

    //获取作者名
    public static function getAuthorName(){
        $authors=Author::findAll(['status'=>1]);
        $AuthorName=[];
        foreach ($authors as $author){
            $AuthorName[$author->id]=$author->name;
        }
        return $AuthorName;
    }

    //获取归属出版社或业务员名
    public static function getInformationName(){
        $rows=Information::find()->where(['<','type',2])->all();
        $listName=[];
        $listName['']='请选择...';
        foreach ( $rows as $row){
            $listName[$row->id]=$row->name;
        }
        return $listName;
    }

    //关联查询作者
    public function getAuthor(){
        return $this->hasOne(Author::className(),['id'=>'author_id']);
    }

    //关联查询分类名
    public function getCategory(){
        return $this->hasOne(Category::className(),['id'=>'category_id']);
    }

    //转定率查询
    public static function getData($id){
        //观看次数
        $hits=\Yii::$app->db->createCommand("SELECT COUNT(*) FROM reading WHERE DATE_SUB(CURDATE(), INTERVAL 1 MONTH)<=from_unixtime(create_time,'%Y-%m-%d') AND book_id=$id")->queryScalar();
        //购买次数
        $purchase_times=\Yii::$app->db->createCommand("SELECT COUNT(*) FROM consume WHERE DATE_SUB(CURDATE(), INTERVAL 1 MONTH)<=from_unixtime(create_time,'%Y-%m-%d') AND book_id=$id")->queryScalar();
        //转定率计算
        $relust=round(($purchase_times/$hits)*100,1) .'%';

        return  $relust;
    }

}