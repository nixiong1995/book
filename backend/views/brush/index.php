<?php
?>
    <p class="col-lg-9">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['brush/index'])?>">
        <input type="text" name="keyword" class="form-control" placeholder="时间"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>包名</th>
            <th>广告id</th>
            <th>限制点击数</th>
            <th>不限制点击数</th>
            <th>时间</th>
        </tr>
        <?php foreach ($models as $model):?>
            <tr>
                <td><?=$model->id;?></td>
                <td><?=$model->name;?></td>
                <td><?=$model->advert_id;?></td>
                <td><?=$model->click?></td>
                <td><?=$model->unrestricted_click?></td>
                <td><?=$model->date?></td>
            </tr>
        <?php endforeach;?>
    </table>
    <div class="text-muted">合计<?=$pager->totalCount?>条</div>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
    'nextPageLabel' => '下一页',
    'prevPageLabel' => '上一页',
    'firstPageLabel' => '首页',
    'lastPageLabel' => '尾页',
]);
