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
            <th>书名</th>
            <th>销量</th>
            <th>销售金额</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($models as $model):?>
            <tr>
                <td><?=$model->name ?></td>
                <td>111</td>
                <td>111</td>
                <td> </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <p>合计:<?= $pager->totalCount;?></p>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
]);
