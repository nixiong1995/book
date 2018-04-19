<p style="color: red;font-size: 24px">
    <span>结算方:<?=$model->information->name?></span>&emsp;&emsp;
    <span>上月实际销售金额:<?=\backend\models\Settlement::getLastmonthConsume($information_id)?></span>&emsp;&emsp;
    <span>结算金额:<?=sprintf("%.2f",\backend\models\Settlement::getLastmonthConsume($information_id)*0.75)?></span>
</p>
<?php
$form=yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'payable')->textInput();
echo $form->field($model,'paid')->textInput();
echo $form->field($model,'poundage')->textInput();
echo $form->field($model,'remarks')->textarea();
echo'<button type="submit"  class="btn btn-info">提交</button>';
yii\bootstrap\ActiveForm::end();