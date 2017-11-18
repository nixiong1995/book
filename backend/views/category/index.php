<?php
?>
    <p><a href="<?=\yii\helpers\Url::to(['category/add'])?>" class="btn btn-primary">添加分类</a></p>
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>分类名称</th>
            <th>分类简介</th>
            <th>所属频道</th>
            <th>操作</th>
        </tr>
        <?php foreach ($models as $model):?>
            <tr data-id="<?=$model->id?>">
                <td><?=$model->id;?></td>
                <td><?=$model->name;?></td>
                <td><?=$model->intro;?></td>
                <td><?=$model->type?'男频':'女频';?></td>
                <td>
                    <a href="<?=\yii\helpers\Url::to(['category/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-default btn-sm"></a>
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
$url_del=\yii\helpers\Url::to(['category/del']);
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
