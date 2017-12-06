<?php
use kartik\datetime\DateTimePicker;
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'begin_time')->widget(DateTimePicker::classname(), [
    'options' => ['placeholder' => ''],
    'pluginOptions' => [
        'autoclose' => true,
        'todayHighlight' => true,
        'startDate' =>date('Y-m-d'), //设置今天之前的日期不能选择
] ]);
echo $form->field($model,'end_time')->widget(DateTimePicker::classname(), [
    'options' => ['placeholder' => ''],
    'pluginOptions' => [
        'autoclose' => true,
        'todayHighlight' => true,
        'startDate' =>date('Y-m-d'), //设置今天之前的日期不能选择
    ] ]);
echo $form->field($model,'price')->textInput();
echo $form->field($model,'people')->textInput();
echo '<button type="submit" class=" btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();