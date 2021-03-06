<?php
namespace backend\models;
use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;

class Chapter extends ActiveRecord{
    public $file;
    public $is_end;
    private static $partitionIndex_ = null; // 分表ID
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
   /* public static function getBookName(){
        $rows=Book::find()->where(['is_api'=>0])->all();
        $BookName=[];
        foreach ( $rows as $row){
            $BookName[$row->id]=$row->name;
        }
        return $BookName;
    }*/

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
        //获取分表ID
        $result=Chapter::resetPartitionIndex($this->book_id);
        if($result!=0){
            $res=Chapter::findOne(['book_id'=>$this->book_id,'no'=>$this->no]);
            if($res){
                $this->addError('no','该书已存在该章节');
            };
        }else{
            throw  new ForbiddenHttpException('对不起,无可操作数据表');
        }

    }

    //验证修改章节号唯一性
    public function validateEditNo(){
        if(\Yii::$app->request->get('no')!=$this->no){
            //获取分表ID
            $result=Chapter::resetPartitionIndex($this->book_id);
            if($result!=0){
                $res=Chapter::findOne(['book_id'=>$this->book_id,'no'=>$this->no]);
                if($res){
                    $this->addError('no','该书已存在该章节');
                };
            }else{
                throw  new ForbiddenHttpException('对不起,无可操作数据表');
            }

        }
    }

    //验证选择收费章节是否准确
    public function validateIsfreeChapter(){
        //查询该书从多少章开始收费
        $data=\Yii::$app->db->createCommand("SELECT no,is_free FROM book WHERE id=$this->book_id")->queryOne();
        //如果当前章节小于开始收费章节,说明是免费章节

            if($this->no<$data['no'] && $this->is_free==1 && $data['no']!=0){
                $this->addError('is_free','该书从第'.$data['no'].'章节开始收费');

                //如果当前章节大于开始收费章节,说明是收费章节
            }elseif ($this->no>=$data['no'] && $this->is_free==0 && $data['no']!=0){
                $this->addError('is_free','该书从第'.$data['no'].'章节开始收费');
            }


    }


    /**
     * 重置分区id
     * @param int $uid
     */
   public static function resetPartitionIndex($book_id = null) {
       if($book_id>0 && $book_id<=104620){
           self::$partitionIndex_ = 1;
       }elseif ($book_id>104620 && $book_id<=114620){
           self::$partitionIndex_ = 2;
       }elseif ($book_id>114620 && $book_id<=124620){
           self::$partitionIndex_ = 3;
       }elseif ($book_id>124620 && $book_id<=134620){
           self::$partitionIndex_ = 4;
       }elseif ($book_id>134620 && $book_id<=144620){
           self::$partitionIndex_ = 5;
       }elseif ($book_id>144620 && $book_id<=154620){
           self::$partitionIndex_ = 6;
       }elseif ($book_id>154620 && $book_id<=164620){
           self::$partitionIndex_ = 7;
       }elseif ($book_id>164620 && $book_id<=174620){
           self::$partitionIndex_ = 8;
       }elseif ($book_id>174620 && $book_id<=184620){
           self::$partitionIndex_ = 9;
       }elseif ($book_id>184620 && $book_id<=194620){
           self::$partitionIndex_ = 10;
       }elseif ($book_id>194620 && $book_id<=204620){
           self::$partitionIndex_ = 11;
       }elseif ($book_id>204620 && $book_id<=214620){
           self::$partitionIndex_ = 12;
       }elseif ($book_id>214620 && $book_id<=224620){
           self::$partitionIndex_ = 13;
       }elseif ($book_id>224620 && $book_id<=234620){
           self::$partitionIndex_ = 14;
       }elseif ($book_id>234620 && $book_id<=244620){
           self::$partitionIndex_ = 15;
       }elseif ($book_id>244620 && $book_id<=254620){
           self::$partitionIndex_ = 16;
       }elseif ($book_id>254620 && $book_id<=264620){
           self::$partitionIndex_ = 17;
       }elseif ($book_id>264620 && $book_id<=274620){
           self::$partitionIndex_ = 18;
       }elseif ($book_id>274620 && $book_id<=284620){
           self::$partitionIndex_ = 19;
       }elseif ($book_id>284620 && $book_id<=294620){
           self::$partitionIndex_ = 20;
       }elseif ($book_id>294620 && $book_id<=304620){
           self::$partitionIndex_ = 21;
       }elseif ($book_id>304620 && $book_id<=314620){
           self::$partitionIndex_ = 22;
       }elseif ($book_id>314620 && $book_id<=324620){
           self::$partitionIndex_ = 23;
       }elseif ($book_id>324620 && $book_id<=334620){
           self::$partitionIndex_ = 24;
       }elseif ($book_id>334620 && $book_id<=344620){
           self::$partitionIndex_ = 25;
       }elseif ($book_id>344620 && $book_id<=354620){
           self::$partitionIndex_ = 26;
       }elseif ($book_id>354620 && $book_id<=364620){
           self::$partitionIndex_ = 27;
       }elseif ($book_id>364620 && $book_id<=374620){
           self::$partitionIndex_ = 28;
       }elseif ($book_id>374620 && $book_id<=384620){
           self::$partitionIndex_ = 29;
       }elseif ($book_id>384620 && $book_id<=394620){
           self::$partitionIndex_ = 30;
       }elseif ($book_id>394620 && $book_id<=404620){
           self::$partitionIndex_ = 31;
       }elseif ($book_id>404620 && $book_id<=414620){
           self::$partitionIndex_ = 32;
       }elseif ($book_id>414620 && $book_id<=424620){
           self::$partitionIndex_ = 33;
       }elseif ($book_id>424620 && $book_id<=434620){
           self::$partitionIndex_ = 34;
       }elseif ($book_id>434620 && $book_id<=444620){
           self::$partitionIndex_ = 35;
       }elseif ($book_id>444620 && $book_id<=454620){
           self::$partitionIndex_ = 36;
       }elseif ($book_id>454620 && $book_id<=464620){
           self::$partitionIndex_ = 37;
       }elseif ($book_id>464620 && $book_id<=474620){
           self::$partitionIndex_ = 38;
       }elseif ($book_id>474620 && $book_id<=484620){
           self::$partitionIndex_ = 39;
       }elseif ($book_id>484620 && $book_id<=494620){
           self::$partitionIndex_ = 40;
       }elseif ($book_id>494620 && $book_id<=504620){
           self::$partitionIndex_ = 41;
       } else{
           self::$partitionIndex_ = 0;
       }
    /*if($book_id>0 && $book_id<=19610){
        self::$partitionIndex_ = 1;
    }elseif ($book_id>19610 && $book_id<=19620){
        self::$partitionIndex_ = 2;
    }elseif ($book_id>19620 && $book_id<=19630){
        self::$partitionIndex_ = 3;
    }elseif ($book_id>19630 && $book_id<=19640){
        self::$partitionIndex_ = 4;
    }elseif ($book_id>19640 && $book_id<=19650){
        self::$partitionIndex_ = 5;
    }elseif ($book_id>19650 && $book_id<=19660) {
        self::$partitionIndex_ = 6;
    }*/
       return self::$partitionIndex_;

    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chapter' . self::$partitionIndex_;
    }
}