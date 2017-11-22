<?php
$form=yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'username')->textInput();
echo $form->field($model,'password')->passwordInput();
echo $form->field($model,'email')->textInput(['type'=>'email']);
echo $form->field($model,'status',['inline'=>true])->radioList(['禁用','正常']);
echo'<button type="submit"  class="btn btn-info">提交</button>';
yii\bootstrap\ActiveForm::end();