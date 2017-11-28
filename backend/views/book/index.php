<?php
?>
    <p><a href="<?=\yii\helpers\Url::to(['book/add'])?>" class="btn btn-primary">新增图书</a></p>
    <table id="table_id_example" class="table">
        <thead>
        <tr>
            <th>书名</th>
            <th>作者</th>
            <th>分类</th>
            <th>封面</th>
            <th>是否免费</th>
            <th>文本大小</th>
            <th>文本类型</th>
            <th>观看数</th>
            <th>评分</th>
            <th>上架时间</th>
            <th>今日必读</th>
            <th>推荐时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($models as $model):?>
            <tr data-id="<?=$model->id?>">
                <td><?=$model->name?></td>
                <td><?=$model->author->name?></td>
                <td><?=$model->category->name?></td>
                <td><?=yii\bootstrap\Html::img(HTTP_PATH.$model->image,['class'=>'img-cricle','style'=>'width:70px'])?></td>
                <td><?php if($model->is_free==1){echo 'vip专属';}elseif($model->is_free==2){echo '收费';}else{echo '免费';}?></td>
                <td><?=$model->size?></td>
                <td><?=$model->type?></td>
                <td><?=$model->clicks?></td>
                <td><?=$model->score?></td>
                <td><?=date("Ymd",$model->create_time)?></td>
                <td><?=$model->groom?'是':'否'?></td>
                <td><?=date("Y-m-d H:i:s",$model->groom_time)?></td>
                <td>
                    <a href="<?=\yii\helpers\Url::to(['book/edit','id'=>$model->id])?>"><span class="glyphicon glyphicon-pencil btn btn-primary btn-sm" ></a>
                    <a href="<?=\yii\helpers\Url::to(['chapter/index','id'=>$model->id])?>"><span class="glyphicon glyphicon-file btn btn-default btn-sm"></a>
                    <a href="javascript:;" class="today_read"><span class="glyphicon glyphicon-star btn btn-success btn-sm"></a>
                    <a href="<?=\yii\helpers\Url::to(['seckill/add','book_id'=>$model->id])?>"><span class="glyphicon glyphicon-time btn btn-info btn-sm"></a>
                    <a href="javascript:;" class="delete"><span class="glyphicon glyphicon-remove btn btn-danger btn-sm"></a>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
<?php
/**
 * @var $this \yii\web\View
 */
$this->registerCssFile("@web/datatables/media/css/jquery.dataTables.css");
$this->registerJsFile("@web/datatables/media/js/jquery.dataTables.js",['depends'=>\yii\web\JqueryAsset::className()]);
$del_url=\yii\helpers\Url::to(['book/del']);
$read_url=\yii\helpers\Url::to(['book/today-read']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
        $('.delete').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            if(confirm('你确定要下架该书吗?')){
               $.post("$del_url",{id:id},function(data) {
                   if(data=='success'){
                       alert('下架成功');
                       tr.hide('slow');
                   }else{
                       alert('下架失败');
                   }
               }) 
            }
        })
        $('.today_read').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            if(confirm('你确定要将该书加入今日必读吗?')){
               $.post("$read_url",{id:id},function(data) {
                   if(data=='success'){
                       alert('加入成功');
                       tr.hide('slow');
                   }else{
                       alert('加入失败');
                   }
               }) 
            }
        })
        $(document).ready( function () {
            $(document).ready( function () {
    $('#table_id_example').DataTable({
        language: {
        "sProcessing": "处理中...",
        "sLengthMenu": "显示 _MENU_ 项结果",
        "sZeroRecords": "没有匹配结果",
        "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
        "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
        "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
        "sInfoPostFix": "",
        "sSearch": "搜索:",
        "sUrl": "",
        "sEmptyTable": "表中数据为空",
        "sLoadingRecords": "载入中...",
        "sInfoThousands": ",",
        "oPaginate": {
            "sFirst": "首页",
            "sPrevious": "上页",
            "sNext": "下页",
            "sLast": "末页"
        }
        
        }
    
    });
    
} );
} );

JS

));