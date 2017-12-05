<?php
?>
<h2>男生限免列表</h2>
<p><a href="<?=\yii\helpers\Url::to(['book/index'])?>" class="btn btn-primary">加入书籍</a></p>
<table class="table">
    <tr>
        <th>书名</th>
        <th>加入时间</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr>
            <td><?=$model->name?></td>
            <td><?=date("Y-m-d H:i:s",$model->groom_time)?></td>
        </tr>
    <?php endforeach;?>
</table>
