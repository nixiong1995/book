<?php
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'groom')->dropDownList([1=>'今日必读',2=>'今日限免',3=>'女生限免',4=>'男生限免',5=>'完本限免']);
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();