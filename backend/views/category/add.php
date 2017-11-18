<?php
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name')->textInput();
echo $form->field($model,'intro')->textarea(['rows'=>5]);
echo $form->field($model,'type',['inline'=>true])->radioList(['女频','男频']);
echo $form->field($model,'status',['inline'=>true])->radioList(['隐藏','显示']);
echo'<button type="submit"  class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();