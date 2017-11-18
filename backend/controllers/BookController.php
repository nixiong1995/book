<?php
namespace backend\controllers;
use backend\models\Book;
use yii\web\Controller;

class BookController extends Controller{
    //图书添加
    public function actionAdd(){
        $model=new Book();
        return $this->render('add',['model'=>$model]);
    }
}