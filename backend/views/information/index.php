<?php
?>
<table class="table">
    <thead>
    <tr>
        <th>ID</th>
        <th>名称</th>
        <th>类别</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=$model->id?></td>
            <td><?=$model->name?></td>
            <td><?php if($model->type==0){echo '业务';}elseif($model->type==1){echo '版权方';}elseif($model->type==2){echo '作者';}elseif($model->type==3){echo '网络爬虫';}?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['information/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-primary btn-sm" ></a>
                <a href="javascript:;" class="delete"><span class="glyphicon glyphicon-trash btn btn-danger btn-sm"></a>
            </td>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
    'nextPageLabel' => '下一页',
    'prevPageLabel' => '上一页',
    'firstPageLabel' => '首页',
    'lastPageLabel' => '尾页',
]);
$del_url=\yii\helpers\Url::to(['information/del']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
        $('.delete').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            if(confirm('你确定要删除吗?')){
               $.post("$del_url",{id:id},function(data) {
                   if(data=='success'){
                       alert('删除成功');
                       tr.hide('slow');
                   }else{
                       alert('删除失败');
                   }
               }) 
            }
        })
JS

));