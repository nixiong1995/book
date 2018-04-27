<?php
?>
<p style="color: red; font-size: 16px" >累计参与人数:<?=\frontend\models\Member::getTotalMember()?></p>
<table class="table">
    <tr>
        <th>音频ID</th>
        <th>素材ID</th>
        <th>参与用户ID</th>
        <th>音频内容</th>
        <th>音频时长</th>
        <th>点赞数</th>
        <th>上传时间</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=$model->id?></td>
            <td><?=$model->material_id?></td>
            <td><?=$model->member_id?></td>
            <td><audio src="http://api.voogaa.cn.<?=$model->path?>" controls>
                    别试了，是你的浏览器渣渣
                </audio> </td>
            <td><?=$model->duration?></td>
            <td class="shuangji"><?=$model->praise?></td>
            <td><?=date("Y-m-d H:i:s",$model->create_time)?></td>
            <td class="button">
                <?php if($model->status==0){echo '<button type="button" class="btn btn-info success">通过审核</button> ' ;}else{echo '<button type="button" class="btn btn-danger error">不通过审核</button>';}?>
            </td>
        </tr>
    <?php endforeach;?>
</table>
    <div class="text-muted">合计:<?=$pager->totalCount?>条&emsp;&emsp;通过审核合计:<?=\frontend\models\Photos::getTotalPhotos()?>条</div>

<?php
echo yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
    'nextPageLabel' => '下一页',
    'prevPageLabel' => '上一页',
    'firstPageLabel' => '首页',
    'lastPageLabel' => '尾页',
]);
/**
 * @var $this \yii\web\View
 */
$edit_url=\yii\helpers\Url::to(['activity/audio-check']);
$Modifiedvalue=\yii\helpers\Url::to(['activity/modified-value']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    //审核通过
        $(document).on('click','.success',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
              $.post("$edit_url",{id:id,status:1},function(data) {
                  if(data=='success'){
                   tr.children("td").eq(7).html('<button type="button" class="btn btn-danger error">不通过审核</button>')
                  }else{
                      alert('审核失败');
                  }
              })
        });
        
        //不通过审核
        $(document).on('click','.error',function(){
            var tr=$(this).parents("tr");
            var id=tr.attr('data-id');
              $.post("$edit_url",{id:id,status:0},function(data) {
                  if(data=='success'){
                      tr.children("td").eq(7).html('<button type="button" class="btn btn-info success">通过审核</button>');
                  }else{
                      alert('审核失败');
                  }
              })
        });
        
        $(document).on('dblclick','.shuangji',function(){
             var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            var td = $(this);
            // 根据表格文本创建文本框 并加入表表中--文本框的样式自己调整
            var text = td.text();
            var txt = $("<input type='text'>").val(text);
            txt.blur(function(){
                // 失去焦点，保存值。于服务器交互自己再写,最好ajax
                var newText = $(this).val();
                $.post("$Modifiedvalue",{id:id,praise:newText},function(data) {
                  if(data=='success'){
                       // 移除文本框,显示新值
                $(this).remove();
                td.text(newText);
                  }else{
                      alert('修改失败');
                  }
              })
            });
            td.text("");
            td.append(txt);
});

JS



));