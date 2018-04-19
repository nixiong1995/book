<?php
?>
<table class="table">
    <tr>
        <th>参与用户ID</th>
        <th>照片</th>
        <th>翻牌限制赞数</th>
        <th>上传时间</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=$model->member_id?></td>
            <td><?=yii\bootstrap\Html::img('http://img.user.com'.$model->img,['class'=>'img-cricle','style'=>'width:100px'])?></td>
            <td><?=$model->limit?></td>
            <td><?=date("Y-m-d H:i:s",$model->create_time)?></td>
            <td class="button">
                <?php if($model->status==0){echo '<button type="button" class="btn btn-info success">通过审核</button>';}else{echo '<button type="button" class="btn btn-danger error">不通过审核</button>';}?>

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
$edit_url=\yii\helpers\Url::to(['activity/photo-check']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    //审核通过
        $(document).on('click','.success',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
              $.post("$edit_url",{id:id,status:1},function(data) {
                  if(data=='success'){
                   tr.children("td").eq(4).html('<button type="button" class="btn btn-danger error">不通过审核</button>')
                  }else{
                      alert('审核失败');
                  }
              })
        });
        
        //不通过审核
        $(document).on('click','.error',function() {
            var tr=$(this).parents("tr");
            var id=tr.attr('data-id');
              $.post("$edit_url",{id:id,status:0},function(data) {
                  if(data=='success'){
                      tr.children("td").eq(4).html('<button type="button" class="btn btn-info success">通过审核</button>');
                  }else{
                      alert('审核失败');
                  }
              })
        });
JS

));