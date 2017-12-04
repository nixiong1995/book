<?php
?>
    <p class="col-lg-7">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['consume/count'])?>">
        <input type="text" name="begin_time" class="form-control" placeholder="起始时间"/>
        <input type="text" name="end_time" class="form-control" placeholder="结束时间"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
    <table class="table">
        <thead>
        <tr>
            <th>书名</th>
            <th>销量</th>
            <th>销售金额</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($models as $model):?>
            <tr>
                <td><?=$model['name'] ?></td>
                <td><?=$model["sellCount"] ?></td>
                <td><?=$model["sellMoney"] ?></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>

<?php
/*echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
]);*/
