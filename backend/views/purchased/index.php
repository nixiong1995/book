<?php
?>
    <p class="col-lg-9">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['purchased/index'])?>">
        <input type="text" name="tel" class="form-control" placeholder="手机号"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
    <table class="table">
        <thead>
        <tr>
            <th>账号</th>
            <th>手机</th>
            <th>书名</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($models as $model):?>
            <tr>
                <td><?=$model->user->uid ?></td>
                <td><?=$model->user->tel?></td>
                <td><?=$model->book->name?></td>
                <td> <a tabindex="0" class="btn btn-sm btn-default" role="button" data-toggle="popover" data-trigger="focus" title="本书已购买章节如下" data-content="<?=str_replace('|','-',$model->chapter_no)?>">查看已购买章节</a></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <p>合计:<?= $pager->totalCount;?></p>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
    'nextPageLabel' => '下一页',
    'prevPageLabel' => '上一页',
    'firstPageLabel' => '首页',
    'lastPageLabel' => '尾页',
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
