<?php
?>
    <table id="table_id_example" class="table">
        <thead>
        <tr>
            <th>账号</th>
            <th>手机</th>
            <th>书名</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($models as $model):?>
            <tr>
                <td><?=$model->user->uid ?></td>
                <td><?=$model->user->tel?></td>
                <td><?=$model->book->name?></td>
                <td> <a tabindex="0" class="btn btn-sm btn-default" role="button" data-toggle="popover" data-trigger="focus" title="本书已购买章节如下" data-content="<?=str_replace('|','-',$model->chapter_no)?>">查看已购买章节</a></td>
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
        $(function () {
  $('[data-toggle="popover"]').popover()
})

JS

));