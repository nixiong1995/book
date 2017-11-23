<?php
?>
<p><a href="<?=\yii\helpers\Url::to(['role/add'])?>" class="btn btn-primary">添加角色</a></p>
<table class="table">
    <tr>
        <th>角色名称</th>
        <th>描述</th>
        <th>操作</th>
    </tr>
    <?php foreach ($roles as $role):?>
        <tr data-name="<?=$role->name?>">
            <td><?=$role->name?></td>
            <td><?=$role->description?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['role/edit','name'=>$role->name])?>"><span class="glyphicon glyphicon-pencil btn btn-primary btn-sm" ></a>
                <a href="javascript:;" class="delete"><span class="glyphicon glyphicon-trash btn btn-danger btn-sm"></a>
            </td>
        </tr>
    <?php endforeach;?>
</table>
<?php
/**
 * @var $this \yii\web\View
 */
$del_url=\yii\helpers\Url::to(['role/del']);
$this->registerJs(new \yii\web\JsExpression(
        <<<JS
        $('.delete').on('click',function() {
            var tr=$(this).closest('tr');
            var name=tr.attr('data-name');
            if(confirm('你确定要删除吗?')){
                $.post("$del_url",{name:name},function(data) {
                    if(data=='success'){
                        alert('删除成功');
                        tr.hide('slow');
                    }else {
                        alert('删除失败');
                    }
                  
                })
                
            }
          
        })

JS

));
