<?php
?>
    <p><a href="<?=\yii\helpers\Url::to(['app/add'])?>" class="btn btn-primary">发布新app</a></p>
    <table class="table">
        <tr>
            <th>版本号</th>
            <th>类型</th>
            <th>创建时间</th>
            <th>修改时间</th>
            <th>操作</th>
        </tr>
        <?php foreach ($models as $model):?>
            <tr data-id="<?=$model->id?>">
                <td><?=$model->version?></td>
                <td><?php if($model->type==1){echo '普通更新';}elseif($model->type==2){echo '强制更新';}else{echo '热更新';}?></td>
                <td><?=date("Y-m-d",$model->create_time)?></td>
                <td><?=date("Y-m-d",$model->update_time)?></td>
                <td>
                    <a href="<?=\yii\helpers\Url::to(['app/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-default btn-sm"></a>
                    <a href="javascript:;" class="delete"><span class="glyphicon glyphicon-trash btn btn-danger btn-sm" ></a>
                </td>
            </tr>
        <?php endforeach;?>
    </table>
<?php
/**
 * @var $this \yii\web\View
 */
$url_del=\yii\helpers\Url::to(['app/del']);
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