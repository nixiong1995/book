<?php
$form=yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'old_password')->passwordInput();
echo $form->field($model,'new_password')->passwordInput();
echo $form->field($model,'repeat_new_password')->passwordInput();
echo $form->field($model,'code')->widget(yii\captcha\Captcha::className(),[
    'captchaAction'=>'modify-password/captcha']);

echo '<button type="submit" class="btn btn-info">提交</button>';
yii\bootstrap\ActiveForm::end();
