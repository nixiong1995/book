<?php
?>
<h2>作者推荐列表</h2>
<p><a href="<?=\yii\helpers\Url::to(['author/index'])?>" class="btn btn-primary">推荐作者</a></p>
<table class="table">
    <tr>
        <th>作者姓名</th>
        <th>加入时间</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr>
            <td><?=$model->name?></td>
            <td><?php if($model->name){echo date("Y-m-d H:i:s",$model->hot_time);}?></td>
        </tr>
    <?php endforeach;?>
</table>
