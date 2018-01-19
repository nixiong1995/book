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
            //[['name','is_free','intro','category_id','no','type','from','clicks','score','sale','downloads','search','price'],'required'],
            [['name','is_free','intro','category_id','no','type','from','price','ascription','clicks','downloads','score','collection'],'required'],
            ['file','required','on'=>self::SCENARIO_ADD],
            ['name', 'unique','message' => '该书已存在.'],
           // ['file', 'file', 'extensions' => ['png', 'jpg', 'gif','jpeg']],
            ['type', 'in', 'range' => ['txt', 'epub'],'message'=>'只能输入txt,epub文本类型'],
            [['no','clicks','score','sale','downloads','search'],'integer'],
            ['score', 'in', 'range' => [0,1, 2, 3,4,5,6,7,8,9,10],'message'=>'只能输入0-10的整数'],
            [['ascription','author_name','price'],'safe'],
            ['ascription','validateFromAscription'],
            ['no','validateNO'],
            ['price','validatePrice'],

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
            'clicks'=>'该书观看次数',
            'no'=>'多少章节开始收费(整本书不收费请输入0)',
            'score'=>'该书评分',
            'intro'=>'该书简介',
            'type'=>'文本类型',
            'from'=>'书来自于(你的选择关系重大,请核对清楚再进行选择)',
            'ascription'=>'书归属于(你的选择关系重大,请核对清楚再进行选择)',
            'author_intro'=>'作者简介',
            'file2'=>'作者图片',
            'sale'=>'该书销售次数',
            'downloads'=>'该书下载次数',
            'search'=>'该书被搜索次数',
            'collection'=>'该书被收藏次数',
            'price'=>'该书售价(阅票/千字)',
        ];
    }

    //搜索获取分类名
    public static function getCategoryName(){
        $rows=Category::find()->all();
        $CategoryName=[];
        $CategoryName['']='请选择...';
        foreach ( $rows as $row){
            $CategoryName[$row->id]=$row->name;
        }
        return $CategoryName;
    }

    //获取作者名
    public static function getAuthorName(){
        $authors=Author::find()->all();
        $AuthorName=[];
        foreach ($authors as $author){
            $AuthorName[$author->id]=$author->name;
        }
        return $AuthorName;
    }

    //获取归属出版社或业务员名
    public static function getInformationName(){
        $rows=Information::find()->all();
        $listName=[];
        $listName['']='请选择...';
        foreach ( $rows as $row){
            $listName[$row->id]=$row->name;
        }
        return $listName;
    }

    //获取推荐分类名称
    public static function getRecommendCategory(){
        $categorys=Category::find()->orderBy('groom_time desc')->limit(5)->all();
        $listName=[];
        $listName['']='请选择...';
        foreach ( $categorys as $category){
            $listName[$category->id]=$category->name;
        }
        return $listName;

    }

    //关联查询作者
    public function getAuthor(){
        return $this->hasOne(Author::className(),['id'=>'author_id']);
    }

    //关联查询图书归属
    public function getInformation(){
        return $this->hasOne(Information::className(),['id'=>'ascription']);
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

    //验证来自和归属选择是否正确
    public function validateFromAscription(){

        //根据选择的书归属,查询这本书的出处
        $data=Information::findOne(['id'=>$this->ascription]);
        //$this->addError('ascription',$data->type);
        //如果选择得是1,说明这本书来自1(定制).判断他的type是否等于2(作者)
        if($this->from==1 && $data->type!=2){
            $this->addError('ascription','该书属于签约图书,请选择对应作者');
            //如果选择得是2,说明这本书来自2(签约).判断他的type是否等于2(作者)
        }elseif ($this->from==2 && $data->type!=2){
            $this->addError('ascription','该书属于定制图书,请选择对应作者');
            //如果选择得是3,说明这本书来自3(版权).判断他的type是否等于1(版权方)
        }elseif ($this->from==3 && $data->type!=1){
            $this->addError('ascription','该书属于版权方图书,请选择对应版权方');
            //如果选择得是4,说明这本书来自4(爬虫).判断他的type是否等于3(网络爬虫)
        }elseif ($this->from==4 && $data->type!=3){
            $this->addError('ascription','该书属于网络爬虫图书,请选择网络爬虫');
        }
    }

    //验证为收费书和vip专属书书,no不能为0
    public function validateNO(){
        if($this->is_free==1 ||$this->is_free==2){
            if($this->no==0){
                $this->addError('no','收费书,收费章节开始数不能为0');
            }
        }
    }

    //验证为收费书和vip专属书书,price不能为0
    public function validatePrice(){
        if($this->is_free==1 ||$this->is_free==2){
            if($this->price==0){
                $this->addError('price','收费书,收费章节开始数不能为0');
            }
        }
    }

}