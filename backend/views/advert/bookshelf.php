<?php
?>
<h2>书架广告列表(客户端显示前6条)</h2>
<p><a href="<?=\yii\helpers\Url::to(['advert/add'])?>" class="btn btn-primary">新增广告</a></p>
    <p class="col-lg-5">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['book/index'])?>">
        <?=\yii\bootstrap\Html::dropDownList('category','0',\backend\models\Book::getCategoryName(),['class'=>"form-control"])?>
        <input type="text" name="book" class="form-control" placeholder="书名"/>
        <input type="text" name="author" class="form-control" placeholder="作者"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
<table class="table">
    <tr>
        <th>广告图片</th>
        <th>排序</th>
        <th>点击数</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=yii\bootstrap\Html::img(HTTP_PATH.$model->image,['class'=>'img-cricle','style'=>'width:70px'])?></td>
            <td><?=$model->sort?></td>
            <td><?=$model->count?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['advert/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-default btn-sm"></a>
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
$url_del=\yii\helpers\Url::to(['advert/del']);
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

