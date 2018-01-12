<?php
?>
    <h2>版权方图书类表</h2>
    <div class=" button form-inline">
        <button class="btn btn-default" id="checkall">全选</button>
        <button class="btn btn-default" id="nocheck">全不选</button>
        <button class="btn btn-default" id="check1">反选</button>
        <?=\yii\bootstrap\Html::dropDownList('category','0',\backend\models\Book::getCategoryName(),['class'=>"form-control"])?>
        <button class="btn btn-succes" id="update">修改分类</button>
    </div>


    <p class="col-lg-5">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['copyright/index'])?>">
        <?=\yii\bootstrap\Html::dropDownList('category','0',\backend\models\Book::getCategoryName(),['class'=>"form-control"])?>
        <input type="text" name="book" class="form-control" placeholder="书名"/>
        <input type="text" name="author" class="form-control" placeholder="作者"/>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
<table class="table">
    <thead>
    <tr>
        <th></th>
        <th>书名</th>
        <th>作者</th>
        <th>分类</th>
        <th>封面</th>
        <th>是否免费</th>
        <th>观看数</th>
        <th>本月转定率</th>
        <th>评分</th>
        <th>销售阅票累计</th>
        <th>书本大小</th>
        <th>状态</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($models as $model):?>
        <tr data-id="<?=$model->id?>">
            <td> <input type="checkbox" name="items" value="<?=$model->id?>"/></td>
            <td><?=$model->name?></td>
            <td><?=$model->author->name?></td>
            <td><?=$model->category->name?></td>
            <td><?=yii\bootstrap\Html::img($model->image,['class'=>'img-cricle','style'=>'width:70px'])?></td>
            <td><?php if($model->is_free==1){echo 'vip专属';}elseif($model->is_free==2){echo '收费';}else{echo '免费';}?></td>
            <td><?=$model->clicks?></td>
            <td><?php echo @\backend\models\Book::getData($model->id)?></td>
            <td><?=$model->score?></td>
            <td><?=$model->ticket?></td>
            <td><?=\backend\models\Chapter::getSize($model->size)?></td>
            <td><?php if($model->is_end==1){echo '连载';}else{echo '完结';}?></td>
            <td>
                <a href="<?=\yii\helpers\Url::to(['copyright/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-primary btn-sm" ></a>
                <a href="javascript:;" class="today_read"><span class="glyphicon glyphicon-star btn btn-success btn-sm"></a>
                <a href="<?=\yii\helpers\Url::to(['seckill/add','book_id'=>$model->id])?>"><span class="glyphicon glyphicon-time btn btn-info btn-sm"></a>
                <a href="<?=\yii\helpers\Url::to(['book/groom','book_id'=>$model->id])?>"><span class="glyphicon glyphicon-star-empty btn btn-default btn-sm"></a>
                <a href="javascript:;" class="delete"><span class="glyphicon glyphicon-remove btn btn-danger btn-sm"></a>
                <a href="<?=\yii\helpers\Url::to(['book/details','book_id'=>$model->id])?>"><span class="glyphicon glyphicon-list-alt btn btn-info btn-sm"></a>
            </td>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>
    <p>数据库图书合计:<?=$total1?>/版权图书<?=$total4?>/本地图书:<?=$total2?>/爬虫图书:<?=$total3?>/该分类图书:<?= $pager->totalCount;?></p>
<?php
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pager,
]);
$del_url=\yii\helpers\Url::to(['copyright/del']);
$sele_url=\yii\helpers\Url::to(['book/selected']);//加入分类精选url
$update_url=\yii\helpers\Url::to(['book/update']);//批量修改分类url
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
        $('.delete').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            if(confirm('你确定要删除该书吗?')){
               $.post("$del_url",{id:id},function(data) {
                   if(data=='success'){
                       alert('删除成功');
                       tr.hide('slow');
                   }else{
                       alert('删除失败');
                   }
               }) 
            }
        });


        $('.today_read').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            if(confirm('你确定要将该书加入分类精选吗?')){
               $.post("$sele_url",{id:id},function(data) {
                   if(data=='success'){
                       alert('加入成功');
                   }else{
                       alert('加入失败');
                   }
               }) 
            }
        });
        
        $(document).ready(function(){
            //全选
            $('#checkall').click(function(){
            $('[name=items]:checkbox').prop('checked',true);
            });
            //全不选
            $('#nocheck').click(function(){
            $('[name=items]:checkbox').prop('checked',false);
            });
            //反选
            $('#check1').click(function(){
            $('[name=items]:checkbox').each(function(){
            this.checked=!this.checked;
            });
            });
        });
          //获取选中的书id和分类id,发送到后台修改分类
         $("#update").click(function() {
            var chk_value =[]; 
            //获取选中的书id
            $('input[name="items"]:checked').each(function(){ 
            chk_value.push($(this).val()); 
            }); 
            //获取选中的分类id
            var category_id=$(".form-control").val();
            if(category_id==0){
                alert('请选择书和分类之后再进行操作');
                return false;
            }
            //发送到后台修改分类
            $.post("$update_url",{book_id:chk_value,category_id:category_id},function(data) {
                if(data=='success'){
                       alert('批量修改分类成功');
                   }else{
                       alert('批量修改分类失败');
                   }
              
            })
         // alert(category_id);
        //alert(chk_value.length==0 ?'你还没有选择任何内容！':chk_value);
         })
        
                
        
JS

));
