<?php
?>
    <p class="col-lg-4">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['consume/index'])?>">
        <input type="text" name="tel" class="form-control" placeholder="手机号"/>
        <input type="text" name="begin_time" class="form-control" placeholder="起始时间"/>
        <input type="text" name="end_time" class="form-control" placeholder="结束时间"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
<table class="table">
        <thead>
        <tr>
            <th>手机</th>
            <th>书名</th>
            <th>消费阅票</th>
            <th>书券抵扣</th>
            <th>实际扣除阅票</th>
            <th>消费时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
   <?php foreach ($models as $model):?>
    <tr>
        <td><?=$model->user->tel?></td>
        <td><?=$model->book->name?></td>
        <td><?=$model->consumption?></td>
        <td><?=$model->deductible?></td>
        <td><?=$model->deduction?></td>
        <td><?=date("Ymd",$model->create_time)?></td>
        <td><a tabindex="0" class="btn btn-sm btn-default" role="button" data-toggle="popover" data-trigger="focus" title="消费章节详细如下" data-content="<?=str_replace('|','-',$model->content)?>">查看消费章节详情</a></td></td>
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
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
        $(function () {
  $('[data-toggle="popover"]').popover()
})
        

JS

));
