<?php
use kartik\file\FileInput;
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'title')->textInput();
echo $form->field($model,'position')->dropDownList([1=>'书架',2=>'书城首页',3=>'书城排行页',4=>'书城精品页',5=>'书城星本页',6=>'书城免费页',7=>'书城完本页',8=>'支付页面']);
echo $form->field($model, 'file')->widget(FileInput::classname(),[
    'options'=>['multiple'=>false],
]);
echo $form->field($model,'url')->textInput();
echo $model->file?yii\bootstrap\Html::img(HTTP_PATH.$model->image,['class'=>'img-cricle','style'=>'width:200px']):'';
echo $form->field($model,'count')->textInput();
echo $form->field($model,'sort')->textInput();
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();
