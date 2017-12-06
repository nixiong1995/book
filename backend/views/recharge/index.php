<?php
?>
    <p class="col-lg-2">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['recharge/index'])?>">
        <input type="text" name="tel" class="form-control" placeholder="手机号"/>
        <input type="text" name="mode" class="form-control" placeholder="充值方式"/>
        <input type="text" name="begin_time" class="form-control" placeholder="起始时间"/>
        <input type="text" name="end_time" class="form-control" placeholder="结束时间"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
    <table class="table">
        <thead>
        <tr>
            <th>交易号</th>
            <th>手机</th>
            <th>充值金额</th>
            <th>所得阅票</th>
            <th>赠送书券</th>
            <th>充值方式</th>
            <th>充值时间</th>
        </tr>
        </thead>
        <tbody>
        <?php $totalMonry=0;?>
        <?php foreach ($models as $model):?>
            <tr>
                <td><?=$model->trade_no ?></td>
                <td><?=$model->user->tel?></td>
                <td><?=$model->money?></td>
                <td><?=$model->ticket?></td>
                <td><?=$model->voucher?></td>
                <td><?=$model->mode?></td>
                <td><?=date("Ymd",$model->create_time)?></td>
            </tr>
            <?php $totalMonry+=$model->money;?>
        <?php endforeach;?>
        </tbody>
    </table>
    <p>数据合计:<?= $pager->totalCount;?>条&emsp;&emsp;充值金额统计:<?=$totalMonry?>.00</p>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
]);
