<?php
$form=yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'username')->textInput();
echo $form->field($model,'password')->passwordInput();
echo $form->field($model,'code')->widget(yii\captcha\Captcha::className(),[
    'captchaAction'=>'admin/captcha']);
echo $form->field($model,'remember')->checkbox();
echo '<button type="submit" class="btn btn-info">登录</button>';
yii\bootstrap\ActiveForm::end();