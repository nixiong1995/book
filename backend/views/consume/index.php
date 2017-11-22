<?php
?>
<table id="table_id_example" class="table">
        <thead>
        <tr>
            <th>手机</th>
            <th>消费阅票</th>
            <th>消费内容</th>
            <th>书券抵扣</th>
            <th>实际扣除阅票</th>
            <th>消费时间</th>
        </tr>
        </thead>
        <tbody>
   <?php foreach ($models as $model):?>
    <tr>
        <td><?=$model->user->tel?></td>
        <td><?=$model->consumption?></td>
        <td><?=$model->content?></td>
        <td><?=$model->deductible?></td>
        <td><?=$model->deduction?></td>
        <td><?=date("Ymd",$model->create_time)?></td>
    </tr>
    <?php endforeach;?>
    </tbody>
    </table>
<?php
/**
 * @var $this \yii\web\View
 */
$this->registerCssFile("@web/datatables/media/css/jquery.dataTables.css");
$this->registerJsFile("@web/datatables/media/js/jquery.dataTables.js",['depends'=>\yii\web\JqueryAsset::className()]);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
        $(document).ready( function () {
    $('#table_id_example').DataTable();
} );

JS

));