<?php
use kartik\select2\Select2;
$form=\yii\bootstrap\ActiveForm::begin();
$data = \backend\models\Chapter::getBookName();
echo $form->field($model,'book_id')->widget(Select2::classname(), [
    'data' => $data,
    'options' => ['placeholder' => '请选择 ...'],
]);
echo $form->field($model,'no')->textInput();
echo $form->field($model,'chapter_name')->textInput();
echo $form->field($model,'file')->fileInput();
echo "<h2 class=\"filename\" data-name='$model->chapter_name'></h2>";
echo $form->field($model,'is_free',['inline'=>true])->radioList(['免费','收费']);
echo $form->field($model,'is_end')->checkbox();
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();
/**
 * @var $this \yii\web\View
 */
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
      
    var name=$("#chapter-chapter_name").val();
    if(name){
        $(".filename").text('已上传章节:'+name)
    }

JS

));