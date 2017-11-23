<?php
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name')->textInput();
echo $form->field($model,'describe')->textInput();
echo $form->field($model,'premissions',['inline'=>true])->checkboxList(\backend\models\RoleForm::getPermissionItems());
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();