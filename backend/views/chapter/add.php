<?php
use kartik\select2\Select2;
use yii\web\JsExpression;
//$url_name=\yii\helpers\Url::to(['chapter/search-title']);
$form=\yii\bootstrap\ActiveForm::begin();
//$data = \backend\models\Chapter::getBookName();
echo $form->field($model,'book_id')->widget(Select2::classname(), [
    //'data' => $data,
    'options' => ['placeholder' => '请选择 ...'],
    'pluginOptions' => [
        'placeholder' => 'search ...',
        'allowClear' => true,
        'language' => [
            'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
        ],
        'ajax' => [
            'url' => \yii\helpers\Url::to(['chapter/search-title']),
            'dataType' => 'json',
            'data' => new JsExpression('function(params) { return {q:params.term}; }')
        ],
        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        'templateResult' => new JsExpression('function(res) { return res.text; }'),
        'templateSelection' => new JsExpression('function (res) { return res.text; }'),
    ],
]);
echo $form->field($model,'no')->textInput();
echo $form->field($model,'chapter_name')->textInput();
echo $form->field($model,'file')->fileInput();
echo "<h2 class=\"filename\" data-name='$model->chapter_name'></h2>";
echo $form->field($model,'is_free',['inline'=>true])->radioList(['免费','收费']);
echo $form->field($model,'is_end',['inline'=>true])->radioList([1=>'连载',2=>'完结']);
echo '<button type="submit" class="btn btn-info">提交</button>';
\yii\bootstrap\ActiveForm::end();
/**
 * @var $this \yii\web\View
 */
$res=is_file(BOOK_PATH.$model->path);//判断书文件是否存在
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    var name=$("#chapter-chapter_name").val();
    if("$res"){
        $(".filename").text('已上传章节文件:'+"$model->path")
    }

JS

));