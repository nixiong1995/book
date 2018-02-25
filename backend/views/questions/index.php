<?php
?>
    <p class="col-lg-9">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['questions/index'])?>">
        <input type="text" name="keyword" class="form-control" placeholder="题目"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
<table class="table">
    <tr>
        <th>题目</th>
        <th>答案a</th>
        <th>答案b</th>
        <th>答案c</th>
        <th>答案d</th>
        <th>正确答案</th>
        <th>状态</th>
        <th>出题ID</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td><?=$model->title?></td>
            <td><?=$model->a?></td>
            <td><?=$model->b?></td>
            <td><?=$model->c?></td>
            <td><?=$model->d?></td>
            <td style="color: red"><?=$model->correct?></td>
            <td class="content"><?php if($model->status==1){echo '审核中';}elseif($model->status==2){echo '未通过审核';}elseif($model->status==3){echo '已有此题';}elseif($model->status==4){echo '通过审核';}?></td>
            <td><?=$model->ascription?></td>
            <td class="button"><?php if($model->status==1){echo '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        审核 <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:;" class="error">不通过审核</a></li>
                        <li><a href="javascript:;" class="warn">已有此题</a></li>
                        <li><a href="javascript:;" class="success">通过审核</a></li>
                    </ul>
                </div>';}else{echo '已审核';}?>

            </td>
        </tr>
    <?php endforeach;?>
</table>

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
$edit_url=\yii\helpers\Url::to(['questions/edit']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    //审核不通过
        $('.error').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
          if(confirm('你确定要执行该操作吗?')){
              $.post("$edit_url",{id:id,status:2},function(data) {
                  if(data=='success'){
                      alert('审核成功');
                     tr.children("td").eq(6).text('未通过审核');
                     tr.children("td").eq(8).text('已审核');
                  }else{
                      alert('审核失败');
                  }
              })
          }
           
        });
        
        //已有此题
        $('.warn').on('click',function() {
            var tr=$(this).parents("tr");
            var id=tr.attr('data-id');
          if(confirm('你确定要执行该操作吗?')){
              $.post("$edit_url",{id:id,status:3},function(data) {
                  if(data=='success'){
                      alert('审核成功');
                      tr.children("td").eq(6).text('已有此题');
                      tr.children("td").eq(8).text('已审核');
                  }else{
                      alert('审核失败');
                  }
              })
          }
        });
        
        //通过审核
        $('.success').on('click',function() {
            var tr=$(this).parents("tr");
            var id=tr.attr('data-id');
          if(confirm('你确定要执行该操作吗?')){
              $.post("$edit_url",{id:id,status:4},function(data) {
                  if(data=='success'){
                      alert('审核成功');
                      tr.children("td").eq(6).text('通过审核');
                      tr.children("td").eq(8).text('已审核');
                  }else{
                      alert('审核失败');
                  }
              })
          }
        })

JS

));