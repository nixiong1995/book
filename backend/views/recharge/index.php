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
            <th>状态</th>
            <th>充值时间</th>
            <th>完成时间</th>
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
                <td><?php if($model->status==1){echo '待付款';}elseif($model->status==2){echo '交易完成';}elseif($model->status==3){echo '异常交易';}?></td>
                <td><?=date("Ymd",$model->create_time)?></td>
                <td><?=date("Ymd",$model->over_time)?></td>
            </tr>
            <?php $totalMonry+=$model->money;?>
        <?php endforeach;?>
        <tr>
            <td>合计:</td>
            <td></td>
            <td><?=$totalMonry?>.00</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>
    <p>
        数据合计:<?= $pager->totalCount;?>条&emsp;&emsp;
        累计充值合计:<?=\backend\models\Recharge::getToatalMoney()?>&emsp;&emsp;
        近一个月累计充值合计:<?=\backend\models\Recharge::getMonthMoney()?>&emsp;&emsp;
        近7天累计充值合计:<?=\backend\models\Recharge::getWeekMoney()?>&emsp;&emsp;
        今日累计充值合计:<?=\backend\models\Recharge::getTodayMoney()?>&emsp;&emsp;
    </p>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
    'nextPageLabel' => '下一页',
    'prevPageLabel' => '上一页',
    'firstPageLabel' => '首页',
    'lastPageLabel' => '尾页',
]);
