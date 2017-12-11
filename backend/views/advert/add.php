<?php
use kartik\file\FileInput;
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'position')->dropDownList(['书架','书城首页','书城排行页','书城精品页','书城星本页','书城免费页','书城完本页']);
echo $form->field($model, 'file')->widget(FileInput::classname(),[
    'options'=>['multiple'=>false],
]);
echo $model->file?yii\bootstrap\Html::img(HTTP_PATH.$model->image,['class'=>'img-cricle','style'=>'width:200px']):'';
echo $form->field($model,'count')->textInput();
echo $form->field($model,'sort')->textInput();
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();
