<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Chapter extends ActiveRecord{
    public $file;
    public $is_end;
    //常量定义场景
    const SCENARIO_Add ='add';

    public function rules()
    {
        return [
            [['book_id','no','chapter_name','is_free'],'required'],
            ['file','required','on'=>self::SCENARIO_Add],
            ['no','integer'],
            ['chapter_name','unique'],
           // ['file', 'file', 'extensions' => ['txt', 'epub']],
            ['chapter_name','string'],
            ['is_end','safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'book_id'=>'书名',
            'no'=>'章节号(整本书章节号请输入0)',
            'chapter_name'=>'章节名称(整本书章节名称请输入书名)',
            'is_free'=>'是否免费章节',
            'file'=>'书文件',
            'is_end'=>'完结请勾选',
        ];
    }

    //获取书名名
    public static function getBookName(){
        $rows=Book::findAll(['status'=>1]);
        $BookName=[];
        foreach ( $rows as $row){
            $BookName[$row->id]=$row->name;
        }
        return $BookName;
    }

    //关联查询书
    public function getBook(){
        return $this->hasOne(Book::className(),['id'=>'book_id']);
    }

    public static function getSize($filesize) {
        if($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' gb';
        } elseif($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' mb';
        } elseif($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' kb';
        } else {
            $filesize = $filesize . ' bytes';
        }
        return $filesize;
    }
}