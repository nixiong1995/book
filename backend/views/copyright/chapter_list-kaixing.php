<?php
?>
<h3>凯兴章节列表</h3>
<p> <a href="javascript:history.go(-1)" class="btn btn-primary">返回</a></p>
<table class="table table-bordered">
        <tr>
            <th>章节名称</th>
            <th>章节字数</th>
            <th>最后修改时间</th>
            <th>操作</th>
        </tr>
        <?php foreach ($datas as $data):?>
        <tr>
            <td><?=$data['chapter_name'];?></td>
            <td><?=$data['word_count']?></td>
            <td><?=$data['update_time']?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['copyright/chapter-content','copyright_chapter_id'=>$data['chapter_id'],'ascription'=>$ascription,'copyright_book_id'=>$copyright_book_id,'chapter_name'=>$data['chapter_name']])?>"><span class="glyphicon glyphicon-eye-open btn btn-info btn-sm"></a>
            </td>
        </tr>
<?php endforeach;?>
</table>
<div class="text-muted">合计<?php echo count($datas)?>条</div>