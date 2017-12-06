<?php
?>
<h2>限时秒杀列表</h2>
<table class="table">
    <tr>
        <th>书名</th>
        <th>秒杀开始时间</th>
        <th>秒杀结束时间</th>
        <th>金额</th>
        <th>参与人数</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr>
            <td><?=$model->book->name?></td>
            <td><?=$model->begin_time?></td>
            <td><?=$model->end_time?></td>
            <td><?=$model->price?></td>
            <td><?=$model->people?></td>
            <td><a href="<?=\yii\helpers\Url::to(['seckill/add','book_id'=>$model->book_id])?>"><span class="glyphicon glyphicon-pencil btn btn-default btn-sm"></a></td>
        </tr>
    <?php endforeach;?>
</table>
