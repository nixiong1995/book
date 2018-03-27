<?php
?>
    <h2>推荐列表</h2>
    <p>
        <a href="<?=\yii\helpers\Url::to(['book/index'])?>" class="btn btn-primary">加入本地书籍</a>
        <a href="<?=\yii\helpers\Url::to(['copyright/index'])?>" class="btn btn-primary">加入版权书籍</a>
    </p>
    <p class="col-lg-9">
    <form class="form-inline" method="get" action="<?=\yii\helpers\Url::to(['book/groom-index'])?>">
        <?=\yii\bootstrap\Html::dropDownList('type','',[1=>'今日必读',2=>'今日限免',3=>'女生限免',4=>'男生限免',5=>'男生完本限免',6=>'女生完本限免',8=>'排行男生畅销',9=>'排行女生畅销',10=>'排行男生热搜',11=>'排行女生热搜',12=>'排行男生完结',13=>'排行女生完结'],['class'=>"form-control"])?>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search">搜索</span></button>
    </form>
    </p>
    <table class="table">
        <tr>
            <th>书名</th>
            <th>加入时间</th>
            <th>推荐位置</th>
            <th>操作</th>
        </tr>
        <?php foreach ($models as $model):?>
            <tr data-id="<?=$model->id?>">
                <td><?=$model->name?></td>
                <td><?=date("Y-m-d H:i:s",$model->groom_time)?></td>
                <td>
                    <?php switch ($model->groom)
                    {
                        case 1:
                            echo '今日必读';
                            break;
                        case 2:
                            echo '今日限免';
                            break;
                        case 3:
                            echo '女生限免';
                            break;
                        case 4:
                            echo '男生限免';
                            break;
                        case 5:
                            echo '男生完本限免';
                            break;
                        case 6:
                            echo '女生完本限免';
                            break;
                        case 8:
                            echo '排行男生畅销';
                            break;
                        case 9:
                            echo '排行女生畅销';
                            break;
                        case 10:
                            echo '排行男生热搜';
                            break;
                        case 11:
                            echo '排行女生热搜';
                            break;
                        case 12:
                            echo '排行男生完结';
                            break;
                        case 13:
                            echo '排行女生完结';
                            break;
                        default:
                            echo "今日必读";
                    }
                    ?>
                </td>
                <td><a href="javascript:;" class="delete"><span class="glyphicon glyphicon-remove btn btn-danger btn-sm"></a></td>
            </tr>
        <?php endforeach;?>
    </table>
<?php
/**
 * @var $this \yii\web\View
 */
$update_url=\yii\helpers\Url::to(['book/groom-update']);//取消推荐url
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
         $('.delete').on('click',function() {
            var tr=$(this).closest('tr');
            var id=tr.attr('data-id');
            if(confirm('你确定要取消该书推荐吗?')){
               $.post("$update_url",{id:id},function(data) {
                   if(data=='success'){
                       alert('取消成功');
                       tr.hide('slow');
                   }else{
                       alert('取消失败');
                   }
               }) 
            }
        });

JS

));