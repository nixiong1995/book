<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Book extends ActiveRecord{

    public $file;
    public $book;
    public $filename;
    const SCENARIO_ADD ='add';

    public function rules()
    {
        return [
            [['name','author_id','is_free','intro','category_id','no'],'required'],
            [['file','book'],'required','on'=>self::SCENARIO_ADD],
            ['name', 'unique','message' => '该书已存在.'],
            [['size','clicks','score'],'number'],
            ['file', 'file', 'extensions' => ['png', 'jpg', 'gif']],
            ['book', 'file', 'extensions' => ['txt', 'epub']],
            ['type','in','range'=>['txt','epub']],
            [['no','clicks'],'integer'],
            ['score', 'in', 'range' => [1, 2, 3,4,5,6,7,8,9,10],'message'=>'只能输入1-10的整数'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name'=>'书名',
            'author_id'=>'作者',
            'is_free'=>'是否免费',
            'file'=>'封面',
            'category_id'=>'所属分类',
            'clicks'=>'观看数',
            'no'=>'多少章节开始收费',
            'score'=>'评分',
            'intro'=>'简介',
            'book'=>'书文件',
        ];
    }

    //获取分类名
    public static function getCategoryName(){
        $rows=Category::findAll(['status'=>1]);
        $CategoryName=[];
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

    //关联查询作者
    public function getAuthor(){
        return $this->hasOne(Author::className(),['id'=>'author_id']);
    }

    //关联查询分类名
    public function getCategory(){
        return $this->hasOne(Category::className(),['id'=>'category_id']);
    }

}