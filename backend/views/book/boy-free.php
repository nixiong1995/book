<?php
?>
<h2>男生限免列表</h2>
<p>
    <a href="<?=\yii\helpers\Url::to(['book/index'])?>" class="btn btn-primary">加入本地书籍</a>
    <a href="<?=\yii\helpers\Url::to(['copyright/index'])?>" class="btn btn-primary">加入版权书籍</a>
</p>
<table class="table">
    <tr>
        <th>书名</th>
        <th>加入时间</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=$model->name?></td>
            <td><?=date("Y-m-d H:i:s",$model->groom_time)?></td>
            <td><a href="javascript:;" class="delete"><span class="glyphicon glyphicon-remove btn btn-danger btn-sm"></a></td>
        </tr>
    <?php endforeach;?>
</table>
<?php
/**
 * @var $this \yii\web\View
 */
$update_url=\yii\helpers\Url::to(['book/groom-update']);//取消推荐url
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
         $('.delete').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            if(confirm('你确定要取消该书推荐吗?')){
               $.post("$update_url",{id:id},function(data) {
                   if(data=='success'){
                       alert('取消成功');
                       tr.hide('slow');
                   }else{
                       alert('取消失败');
                   }
               }) 
            }
        });

JS

));
