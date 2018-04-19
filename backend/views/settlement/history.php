<?php
?>
<h2 style="color: #00b3ee">历史结算</h2>
<p class="col-lg-4">
<form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['settlement/history'])?>">
    <input type="text" name="information_name" class="form-control" placeholder="结算方名称"/>
    起始时间:<input type="date" name="begin_time" class="form-control" />
    结束时间:<input type="date" name="end_time" class="form-control" />
    <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
</form>
</p>
<table class="table">
<tr>
    <th>结算方</th>
    <th>应付金额</th>
    <th>手续费</th>
    <th>实付金额</th>
    <th>备注</th>
    <th>状态</th>
    <th>结算时间</th>
</tr>
<?php foreach ($models as $model):?>
    <tr>
        <td><?=$model->information->name?></td>
        <td><?=$model->payable?></td>
        <td><?=$model->poundage?></td>
        <td><?=$model->paid?></td>
        <td><?=$model->remarks?></td>
        <td><?php if($model->status==1){echo '<span style="color: green">未结算</span>';}else{echo '<span style="color: red">已结算</span>';}?></td>
        <td><?=date("Y-m-d H:i:s",$model->create_time)?></td>
    </tr>
<?php endforeach;?>
</table>
<?php
echo yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
    'nextPageLabel' => '下一页',
    'prevPageLabel' => '上一页',
    'firstPageLabel' => '首页',
    'lastPageLabel' => '尾页',
]);