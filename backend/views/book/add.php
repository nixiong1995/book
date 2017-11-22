<?php
use kartik\select2\Select2;
use kartik\file\FileInput;
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name')->textInput();
$data1 = \backend\models\Book::getAuthorName();
echo $form->field($model, 'author_id')->widget(Select2::classname(), [
    'data' => $data1,
    'options' => ['placeholder' => '请选择 ...'],
]);
echo $form->field($model, 'file')->widget(FileInput::classname(),[
    'options'=>['multiple'=>false],
]);
echo $model->file?yii\bootstrap\Html::img(HTTP_PATH.$model->image,['class'=>'img-cricle','style'=>'width:200px']):'';
echo $form->field($model, 'book')->fileInput();
echo "<h2 class=\"filename\" data-name='$model->name'></h2>";
$data2 = \backend\models\Book::getCategoryName();
echo $form->field($model, 'category_id')->widget(Select2::classname(), [
    'data' => $data2,
    'options' => ['placeholder' => '请选择 ...'],
]);
echo $form->field($model,'intro')->textarea(['rows'=>5]);
echo $form->field($model,'is_free')->radioList(['免费','收费']);
echo $form->field($model,'clicks')->textInput();
echo $form->field($model,'no')->textInput();
echo $form->field($model,'score')->textInput();
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();
/**
 * @var $this \yii\web\View
 */
$type=$model->type;
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
      
    var name=$("#book-name").val();
    if(name){
        $(".filename").text('已上传小说:'+name+'.'+"$type");
    }


JS

));