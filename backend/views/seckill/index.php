<?php
?>
    <p><a href="<?=\yii\helpers\Url::to(['book/index'])?>" class="btn btn-primary">添加秒杀</a></p>
<h2>限时秒杀列表</h2>
<table class="table">
    <tr>
        <th>书名</th>
        <th>秒杀开始时间</th>
        <th>秒杀结束时间</th>
        <th>金额</th>
        <th>参与人数</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=$model->book->name?></td>
            <td><?=$model->begin_time?></td>
            <td><?=$model->end_time?></td>
            <td><?=$model->price?></td>
            <td><?=$model->people?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['seckill/add','book_id'=>$model->book_id])?>"><span class="glyphicon glyphicon-pencil btn btn-default btn-sm"></a>
                <a href="javascript:;" class="delete"><span class="glyphicon glyphicon-trash btn btn-danger btn-sm" ></a>
            </td>

        </tr>
    <?php endforeach;?>
</table>
<?php
/**
 * @var $this \yii\web\View
 */
$url_del=\yii\helpers\Url::to(['seckill/del']);
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