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
echo $form->field($model,'from')->dropDownList([''=>'请选择...',1=>'签约',2=>'定制',3=>'版权方',4=>'爬虫']);
echo $form->field($model,'ascription')->dropDownList(\backend\models\Book::getInformationName());
echo $form->field($model,'type')->textInput();
echo $form->field($model,'intro')->textarea(['rows'=>5]);
echo $form->field($model,'is_free',['inline'=>true])->radioList(['免费','VIP专属','收费']);
echo $form->field($model,'clicks')->textInput();
echo $form->field($model,'no')->textInput();
echo $form->field($model,'score')->textInput();
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();
