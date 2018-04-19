<?php
?>
<p><a href="<?=\yii\helpers\Url::to(['settlement/history'])?>" class="btn btn-primary">查看历史结算</a></p>
<h2 style="color: #00b3ee">本月结算</h2>
<table class="table">
<tr>
    <th>结算方</th>
    <th>实际销售金额(单位/元)</th>
    <th>结算金额(单位/元)</th>
    <th>操作</th>
</tr>
<?php foreach ($models as $model):?>
    <tr>
        <td><?=$model->name?></td>
        <td><?=\backend\models\Settlement::getMonthConsume($model['id'])?></td>
        <td style="color: red"><?=sprintf("%.2f",\backend\models\Settlement::getMonthConsume($model['id'])*0.75)?></td>
        <td>
           <?php if(\backend\models\Settlement::getRelust($model['id'])){echo '<span style="color: green">上月已结算:'.sprintf("%.2f",\backend\models\Settlement::getLastmonthConsume($model['id'])*0.75).'</span>';}else{echo  '<a href='.\yii\helpers\Url::to(['settlement/add','information_id'=>$model['id']]).'><span class="btn btn-info btn-sm ">上月结算:'.sprintf("%.2f",\backend\models\Settlement::getLastmonthConsume($model['id'])*0.75).'</span></a>' ;}?>
        </td>
    </tr>
<?php endforeach;?>
</table>
