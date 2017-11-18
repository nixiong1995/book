<?php
use kartik\file\FileInput;
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name')->textInput();
echo $form->field($model,'intro')->textarea(['rows'=>5]);
echo $form->field($model, 'file')->widget(FileInput::classname(),[
    'options'=>['multiple'=>false],
]);
echo $model->file?yii\bootstrap\Html::img($model->file,['class'=>'img-cricle','style'=>'width:200px']):'';
echo $form->field($model,'popularity')->textInput();
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();