<?php
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name')->textInput();
echo $form->field($model,'username')->textInput();
echo $form->field($model,'password')->textInput();
echo $form->field($model,'type')->radioList([1=>'版权方',2=>'作者',3=>'网络爬虫']);
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();