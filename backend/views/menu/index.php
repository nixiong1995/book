<?php
?>
    <p><a href="<?=\yii\helpers\Url::to(['menu/add'])?>" class="btn btn-primary">添加菜单</a></p>
<table class="table">
    <tr>
        <th>名称</th>
        <th>路由</th>
        <th>排序</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=$model->parent_id?'---':'',$model->name?></td>
            <td><?=$model->route?></td>
            <td><?=$model->sort?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['menu/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-default btn-sm"></a>
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
$url_del=\yii\helpers\Url::to(['menu/del']);
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

