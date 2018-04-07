<?php
$i=0;
?>
<h3>17K章节列表</h3>
<p> <a href="javascript:history.go(-1)" class="btn btn-primary">返回</a></p>
<table class="table table-bordered">
    <tr>
        <th>章节名称</th>
        <th>章节字数</th>
        <th>最后修改时间</th>
        <th>操作</th>
    </tr>
    <?php foreach ($datas as $data):?>
        <?php foreach ($data->chapters as $row):?>
            <?php $i++?>
        <tr>
            <td><?=$row->name?></td>
            <td><?=$row->word_count?></td>
            <td><?=$row->updated_at?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['copyright/chapter-content','copyright_chapter_id'=>$row->id,'ascription'=>$ascription,'copyright_book_id'=>$copyright_book_id,'chapter_name'=>$row->name])?>"><span class="glyphicon glyphicon-eye-open btn btn-info btn-sm"></a>
            </td>
        </tr>
        <?php endforeach;?>
    <?php endforeach;?>
</table>
<div class="text-muted">合计<?php echo $i?>条</div>
