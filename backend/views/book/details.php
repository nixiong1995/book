<?php
?>
<p>
    <a href="<?=\yii\helpers\Url::to(['book/index'])?>" class="btn btn-primary btn-lg active" role="button">返回本地图书列表</a>
    <a href="<?=\yii\helpers\Url::to(['copyright/index'])?>" class="btn btn-primary btn-lg active" role="button">返回版权图书列表</a>
</p>

<h2>图书详细信息</h2>
<div>
    <table class="table">
        <tr>
            <td>图书ID:</td>
            <td><?=$model->id?></td>
        </tr>
        <tr>
            <td>版权方ID:</td>
            <td><?=$model->copyright_book_id?></td>
        </tr>
        <tr>
            <td>图书名称:</td>
            <td><?=$model->name?></td>
        </tr>
        <tr>
            <td>作者:</td>
            <td><?=$model->author->name?></td>
        </tr>
        <tr>
            <td>分类:</td>
            <td><?=$model->category->name?></td>
        </tr>
        <tr>
            <td>图书来自于:</td>
            <td><?php if($model->from==1){echo '签约';}elseif($model->from==2){echo '定制';}elseif($model->from==3){echo '版权方';}elseif($model->from==4){echo '爬虫';}?></td>
        </tr>
        <tr>
            <td>图书归属于:</td>
            <td><?=$model->information->name?></td>
        </tr>
        <tr>
            <td>图书简介:</td>
            <td><?=$model->intro?></td>
        </tr>
        <tr>
            <td>收费类型:</td>
            <td><?php if($model->is_free==1){echo 'vip专属';}elseif($model->is_free==2){echo '收费';}else{echo '免费';}?></td>
        </tr>
        <tr>
            <td>价格(阅票/千字):</td>
            <td><?=$model->price?></td>
        </tr>
        <tr>
            <td>多少章节开始收费:</td>
            <td><?=$model->no?></td>
        </tr>
        <tr>
            <td>图书观看次数:</td>
            <td><?=$model->clicks?></td>
        </tr>
        <tr>
            <td>图书收藏次数:</td>
            <td><?=$model->collection?></td>
        </tr>
        <tr>
            <td>图书下载次数:</td>
            <td><?=$model->downloads?></td>
        </tr>
        <tr>
            <td>图书搜索次数:</td>
            <td><?=$model->search?></td>
        </tr>
        <tr>
            <td>图书销售次数:</td>
            <td><?=$model->sale?></td>
        </tr>
        <tr>
            <td>图书大小:</td>
            <td><?=\backend\models\Chapter::getSize($model->size)?></td>
        </tr>
        <tr>
            <td>图书评分:</td>
            <td><?=$model->score?></td>
        </tr>
        <tr>
            <td>图书更新状态:</td>
            <td><?php if($model->is_end==1){echo '连载';}else{echo '完结';}?></td>
        </tr>
        <tr>
            <td>最新章节ID:</td>
            <td><?=$model->last_update_chapter_id?></td>
        </tr>
        <tr>
            <td>最新章节名称:</td>
            <td><?=$model->last_update_chapter_name?></td>
        </tr>
        <tr>
            <td>图书累计销售阅票:</td>
            <td><?=$model->ticket?>.00</td>
        </tr>
        <tr>
            <td>最后更新时间:</td>
            <td><?=date("Y-m-d H:i:s",$model->update_time)?></td>
        </tr>
        <tr>
            <td>上架时间:</td>
            <td><?=date("Y-m-d H:i:s",$model->create_time)?></td>
        </tr>
    </table>
</div>