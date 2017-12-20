<?php
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'version')->textInput();
echo $form->field($model,'intro')->textarea(['rows'=>5]);
echo $form->field($model,'type')->dropDownList([''=>'请选择...',1=>'普通更新',2=>'强制更新',3=>'热更新']);
echo $form->field($model,'file')->fileInput();
echo "<h2 class=\"filename\"></h2>";
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();
/**
 * @var $this \yii\web\View
 */
$res=is_file(Yii::getAlias('@webroot').$model->url);//判断书文件是否存在
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    if("$res"){
        $(".filename").text('已上apk文件:'+"$model->url")
    }
JS

));