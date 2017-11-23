<?php
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name')->textInput();
echo $form->field($model,'parent_id')->dropDownList(\backend\models\Menu::getMenuName());
echo $form->field($model,'route')->dropDownList(\backend\models\Menu::getPermissionItems());
echo $form->field($model,'sort')->textInput();
echo '<button type="submit" class=" btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();