<?php
?>
<h2>章节信息列表</h2>
    <p><a href="<?=\yii\helpers\Url::to(['chapter/add'])?>" class="btn btn-primary">新增章节</a></p>
    <p class="col-lg-9">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['chapter/index'])?>">
        <input type="text" name="keyword" class="form-control" placeholder="章节名称/章节号"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
    <table class="table table-bordered">
        <tr>
            <th>章节号</th>
            <th>章节名称</th>
            <th>是否收费章节</th>
            <th>章节大小</th>
            <th>上传时间</th>
            <th>修改时间</th>
            <th>操作</th>
        </tr>
        <?php foreach ($models as $model):?>
            <?php
            $size=0;
            $filename = BOOK_PATH.$model->path;
            if(is_file($filename)){
                $size =filesize($filename);
            }
            ?>
            <tr data-id="<?=$model->id?>">
                <td><?=$model->no;?></td>
                <td><?=$model->chapter_name;?></td>
                <td><?=$model->is_free?'是':'否';?></td>
                <td><?php echo \backend\models\Chapter::getSize($size)?></td>
                <td><?=date("Ymd",$model->create_time);?></td>
                <td><?=date("Ymd",$model->update_time);?></td>
                <td>
                    <a href="<?=\yii\helpers\Url::to(['chapter/edit','id'=>$model->id,'no'=>$model->no])?>"><span class="glyphicon glyphicon-pencil btn btn-default btn-sm"></a>
                    <a href="javascript:;" class="delete"><span class="glyphicon glyphicon-trash btn btn-danger btn-sm" ></a>
                </td>
            </tr>
        <?php endforeach;?>
    </table>
    <div class="text-muted">合计<?=$pager->totalCount?>条</div>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
]);
/**
 * @var $this \yii\web\View
 */
$url_del=\yii\helpers\Url::to(['chapter/del']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
$('.delete').on('click',function() {
    if(confirm('你确定要删除吗?')){
        var tr=$(this).closest('tr');
        var id=tr.attr('data-id');
        $.post("$url_del",{id:id},function(data) {
            if(data=='success'){
                alert('删除成功');
                tr.hide('slow')
            }else{
                alert('删除失败');
            }
        })
    }
})
JS

));

