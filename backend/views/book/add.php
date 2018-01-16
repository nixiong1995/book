<?php
use kartik\select2\Select2;
use kartik\file\FileInput;
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name')->textInput();
/*$data1 = \backend\models\Book::getAuthorName();
echo $form->field($model, 'author_id')->widget(Select2::classname(), [
    'data' => $data1,
    'options' => ['placeholder' => '请选择 ...'],
]);*/
echo $form->field($model,'author_name')->textInput();//作者姓名
echo $form->field($model, 'file')->widget(FileInput::classname(),[
    'options'=>['multiple'=>false],
]);//书封面
echo $model->file?yii\bootstrap\Html::img(HTTP_PATH.$model->image,['class'=>'img-cricle','style'=>'width:200px']):'';
$data2 = \backend\models\Book::getCategoryName();
echo $form->field($model, 'category_id')->widget(Select2::classname(), [
    'data' => $data2,
    'options' => ['placeholder' => '请选择 ...'],
]);
echo $form->field($model,'intro')->textarea(['rows'=>5]);
echo $form->field($model,'from')->dropDownList([''=>'请选择...',1=>'签约',2=>'定制',3=>'版权方',4=>'爬虫']);
echo $form->field($model,'ascription')->dropDownList(\backend\models\Book::getInformationName());
echo $form->field($model,'type')->textInput();
echo $form->field($model,'is_free',['inline'=>true])->radioList(['免费','VIP专属','收费']);
echo $form->field($model,'price')->textInput();
echo $form->field($model,'no')->textInput();
echo '<a class="btn btn-info"> 点击生成以下数值</a>';
echo $form->field($model,'clicks')->textInput();
//echo $form->field($model,'sale')->textInput();
echo $form->field($model,'downloads')->textInput();
echo $form->field($model,'collection')->textInput();
echo $form->field($model,'score')->textInput();
//echo $form->field($model,'search')->textInput();
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();

/**
 * @var $this \yii\web\View
 */
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    //观看随机数
    $('.btn-info').on('click',function() {
        //观看随机数
        var clicks=randomNum(5000,10000);
       $('#book-clicks').val(clicks);
       //下载随机数
        var downloads=randomNum(5000,10000);
       $('#book-downloads').val(downloads);
       //收藏随机数
        var collection=randomNum(5000,10000);
       $('#book-collection').val(collection);
        //随机评分
         var score=randomNum(7,10);
       $('#book-score').val(score);
    });
    
   
    //随机方法
    function randomNum(minNum,maxNum){ 
    switch(arguments.length){ 
        case 1: 
            return parseInt(Math.random()*minNum+1,10); 
        break; 
        case 2: 
            return parseInt(Math.random()*(maxNum-minNum+1)+minNum,10); 
        break; 
            default: 
                return 0; 
            break; 
    } 
} 

JS

));
