<?php
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name')->textInput();

\yii\bootstrap\ActiveForm::end();