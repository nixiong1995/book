<?php
?>
<p><a href="<?=\yii\helpers\Url::to(['author/add'])?>" class="btn btn-primary">新增作者</a></p>
    <p class="col-lg-9">
        <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['author/index'])?>">
            <input type="text" name="keyword" class="form-control" placeholder="作者姓名"/>
            <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
        </form>
    </p>
<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <th>姓名</th>
        <th>简介</th>
        <th>图片</th>
        <th>人气</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=$model->id;?></td>
            <td><?=$model->name;?></td>
            <td><?=$model->intro;?></td>
            <td><?=yii\bootstrap\Html::img('http://upload.book.com/'.$model->image,['class'=>'img-cricle','style'=>'width:70px'])?></td>
            <td><?=$model->popularity?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['author/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-default btn-sm"></a>
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
$url_del=\yii\helpers\Url::to(['author/del']);
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
