<?php
namespace backend\models;
use yii\db\ActiveRecord;

class Chapter extends ActiveRecord{
    public $file;
    public $is_end;
    //常量定义场景
    const SCENARIO_Add ='add';
    const SCENARIO_EDIT ='edit';

    public function rules()
    {
        return [
            [['book_id','no','chapter_name','is_free'],'required'],
           // [['book_id','is_free'],'required'],
            ['file','required','on'=>self::SCENARIO_Add],
            ['no','integer'],
            //['chapter_name','unique'],
            ['file', 'file', 'extensions' => ['txt', 'epub']],
            ['no','validateNo','on'=>self::SCENARIO_Add],
            ['no','validateEditNo','on'=>self::SCENARIO_EDIT],
            ['is_free','validateIsfreeChapter'],
            ['chapter_name','string'],
            ['is_end','safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'book_id'=>'书名',
            'no'=>'章节排序id(整本书请输入0,与图书章节号对应)',
            'chapter_name'=>'章节名称(格式:第X章 XXXXX)',
            'is_free'=>'是否免费章节',
            'file'=>'书文件',
            'is_end'=>'是否完结',
        ];
    }

    //获取书名名
    public static function getBookName(){
        $rows=Book::find()->where(['!=','from',3])->all();
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

    //将书文件字节转换成单位
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

    //验证添加章节号唯一性
    public function validateNo(){
        $res=Chapter::findOne(['book_id'=>$this->book_id,'no'=>$this->no]);
        if($res){
            $this->addError('no','该书已存在该章节');
        };
    }

    //验证修改章节号唯一性
    public function validateEditNo(){
        if(\Yii::$app->request->get('no')!=$this->no){
            $res=Chapter::findOne(['book_id'=>$this->book_id,'no'=>$this->no]);
            if($res){
                $this->addError('no','该书已存在该章节');
            };
        }
    }

    //验证选择收费章节是否准确
    public function validateIsfreeChapter(){
        //查询该书从多少章开始收费
        $IsfreeChapter=\Yii::$app->db->createCommand("SELECT no FROM book WHERE id=$this->book_id")->queryScalar();
        //如果当前章节小于开始收费章节,说明是免费章节
        if($this->no<$IsfreeChapter && $this->is_free==1){
            $this->addError('is_free','该书从第'.$IsfreeChapter.'章节开始收费');

        //如果当前章节大于开始收费章节,说明是收费章节
        }elseif ($this->no>=$IsfreeChapter && $this->is_free==0){
            $this->addError('is_free','该书从第'.$IsfreeChapter.'章节开始收费');
        }
    }
}