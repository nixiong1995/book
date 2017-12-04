<?php
?>
    <p><a href="<?=\yii\helpers\Url::to(['book/add'])?>" class="btn btn-primary">新增图书</a></p>
    <p class="col-lg-5">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['book/index'])?>">
    <?=\yii\bootstrap\Html::dropDownList('category','0',\backend\models\Book::getCategoryName(),['class'=>"form-control"])?>
        <input type="text" name="book" class="form-control" placeholder="书名"/>
        <input type="text" name="author" class="form-control" placeholder="作者"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
    <table class="table">
        <thead>
        <tr>
            <th>书名</th>
            <th>作者</th>
            <th>分类</th>
            <th>封面</th>
            <th>是否免费</th>
            <th>观看数</th>
            <th>评分</th>
            <th>上架时间</th>
            <th>今日必读</th>
            <th>推荐时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($models as $model):?>
            <tr data-id="<?=$model->id?>">
                <td><?=$model->name?></td>
                <td><?=$model->author->name?></td>
                <td><?=$model->category->name?></td>
                <td><?=yii\bootstrap\Html::img(HTTP_PATH.$model->image,['class'=>'img-cricle','style'=>'width:70px'])?></td>
                <td><?php if($model->is_free==1){echo 'vip专属';}elseif($model->is_free==2){echo '收费';}else{echo '免费';}?></td>
                <td><?=$model->clicks?></td>
                <td><?=$model->score?></td>
                <td><?=date("Y-m-d",$model->create_time)?></td>
                <td class="txt"><?=$model->groom?'是':'否'?></td>
                <td><?=date("Y-m-d",$model->groom_time)?></td>
                <td>
                    <a href="<?=\yii\helpers\Url::to(['book/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-primary btn-sm" ></a>
                    <a href="<?=\yii\helpers\Url::to(['chapter/index','id'=>$model->id])?>"><span class="glyphicon glyphicon-file btn btn-default btn-sm"></a>
                    <a href="javascript:;" class="today_read"><span class="glyphicon glyphicon-star btn btn-success btn-sm"></a>
                    <a href="<?=\yii\helpers\Url::to(['seckill/add','book_id'=>$model->id])?>"><span class="glyphicon glyphicon-time btn btn-info btn-sm"></a>
                    <a href="javascript:;" class="delete"><span class="glyphicon glyphicon-remove btn btn-danger btn-sm"></a>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <p>合计:<?= $pager->totalCount;?></p>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
]);
/**
 * @var $this \yii\web\View
 */
$this->registerCssFile("@web/datatables/media/css/jquery.dataTables.css");
$this->registerJsFile("@web/datatables/media/js/jquery.dataTables.js",['depends'=>\yii\web\JqueryAsset::className()]);
$del_url=\yii\helpers\Url::to(['book/del']);
$read_url=\yii\helpers\Url::to(['book/today-read']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
        $('.delete').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            if(confirm('你确定要下架该书吗?')){
               $.post("$del_url",{id:id},function(data) {
                   if(data=='success'){
                       alert('下架成功');
                       tr.hide('slow');
                   }else{
                       alert('下架失败');
                   }
               }) 
            }
        });
        $('.today_read').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            if(confirm('你确定要将该书加入今日必读吗?')){
               $.post("$read_url",{id:id},function(data) {
                   if(data=='success'){
                       alert('加入成功');
                   }else{
                       alert('加入失败');
                   }
               }) 
               alert($(this).parent().find("td:first"))
            }
        });
JS

));