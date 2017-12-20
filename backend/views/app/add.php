<?php
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'version')->textInput();
echo $form->field($model,'intro')->textarea(['rows'=>5]);
echo $form->field($model,'type')->dropDownList([''=>'请选择...',1=>'更新版本',2=>'强制下载']);
echo $form->field($model,'file')->fileInput();
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();