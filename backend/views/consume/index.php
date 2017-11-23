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
            $(document).ready( function () {
    $('#table_id_example').DataTable({
        language: {
        "sProcessing": "处理中...",
        "sLengthMenu": "显示 _MENU_ 项结果",
        "sZeroRecords": "没有匹配结果",
        "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
        "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
        "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
        "sInfoPostFix": "",
        "sSearch": "搜索:",
        "sUrl": "",
        "sEmptyTable": "表中数据为空",
        "sLoadingRecords": "载入中...",
        "sInfoThousands": ",",
        "oPaginate": {
            "sFirst": "首页",
            "sPrevious": "上页",
            "sNext": "下页",
            "sLast": "末页"
        }
        
        }
    
    });
    
} );
} );

JS

));