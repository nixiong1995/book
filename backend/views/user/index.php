<?php
?>
    <h2>用户列表</h2>
<p>
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['user/index'])?>">
        <input type="text" name="keyword" class="form-control" placeholder="手机、账号"/>
        <input type="text" name="address" class="form-control" placeholder="地域"/>
        <input type="text" name="source" class="form-control" placeholder="来源"/>
        <input type="text" name="begin_time" class="form-control" placeholder="开始时间(如20171115)"/>
        <input type="text" name="end_time" class="form-control" placeholder="结束时间(如20171115)"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
</p>
<table class="table">
    <tr>
        <th>ID</th>
        <th>账号</th>
        <th>电话</th>
        <th>邮箱</th>
        <th>地域</th>
        <th>来源</th>
        <th>状态</th>
        <th>注册时间</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=$model->id?></td>
            <td><?=$model->uid?></td>
            <td><?=$model->tel?></td>
            <td><?=$model->email?></td>
            <td><?=$model->address?></td>
            <td><?=$model->source?></td>
            <td class="txt"><?=$model->status?'正常':'禁用'?></td>
            <td><?=date("Y-m-d",$model->created_at)?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['user/detail','id'=>$model->id])?>"><span class="glyphicon glyphicon-file btn btn-default btn-sm"></a>
                <?php if($model->status){echo '<a href="javascript:;" class="ban"><span class="glyphicon glyphicon-remove btn btn-danger btn-sm"></a>';}?>
            </td>
        </tr>
    <?php endforeach;?>
</table>
<p>合计:<?= $pager->totalCount;?>&emsp;&emsp;
    近一个月新增:<?=\backend\models\User::getMonth()?>&emsp;&emsp;
    近7天新增:<?=\backend\models\User::getWeek()?>&emsp;&emsp;
    今日新增:<?=\backend\models\User::getToday()?>&emsp;&emsp;
</p>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
    'nextPageLabel' => '下一页',
    'prevPageLabel' => '上一页',
    'firstPageLabel' => '首页',
    'lastPageLabel' => '尾页',
]);
/**
 * @var $this \yii\web\View
 */
$url_del=\yii\helpers\Url::to(['user/ban']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
$('.ban').on('click',function() {
    if(confirm('你确定要封禁该用户吗?')){
        var tr=$(this).closest('tr');
        var txt=$(this).parent().prev().prev();
        var id=tr.attr('data-id');
        var button=$(this);
        $.post("$url_del",{id:id},function(data) {
            if(data=='success'){
                alert('封禁成功');
                txt.text('禁用');
                button.remove();
            }else if(data=='error'){
                alert('封禁失败');
            }
        })
    }
  
})
JS

));




