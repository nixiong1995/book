<?php
?>
    <p><a href="<?=\yii\helpers\Url::to(['admin/add'])?>" class="btn btn-primary">添加管理员</a></p>
<table class="table">
    <tr>
        <th>用户名</th>
        <th>邮箱</th>
        <th>状态</th>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
    <tr data-id="<?=$model->id?>">
        <td><?=$model->username?></td>
        <td><?=$model->email?></td>
        <td><?=$model->status?'正常':'禁用'?></td>
        <td><?=date("Ymd",$model->created_at)?></td>
        <td>
            <a href="<?=\yii\helpers\Url::to(['admin/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-default btn-sm"></a>
            <a href="javascript:;" class="delete"><span class="glyphicon glyphicon-trash btn btn-danger btn-sm"></a>
        </td>
    </tr>
    <?php endforeach;?>
</table>
<?php
echo yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
]);
/**
 * @var $this \yii\web\View
 */
$del_url=\yii\helpers\Url::to(['admin/del']);
$this->registerJs(new \yii\web\JsExpression(
        <<<JS
        $('.delete').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
          if(confirm('你确定要删除吗?')){
              $.post("$del_url",{id:id},function(data) {
                  if(data=='success'){
                      alert('删除成功');
                      tr.hide('slow');
                  }else{
                      alert('删除失败');
                  }
              })
          }
        })

JS

));