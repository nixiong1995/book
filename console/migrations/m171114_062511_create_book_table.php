<?php

use yii\db\Migration;

/**
 * Handles the creation of table `book`.
 */
class m171114_062511_create_book_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('book', [
            'id' => $this->primaryKey(),
            'name'=>$this->char(10)->notNull()->comment('书名'),
            'author_id'=>$this->integer()->notNull()->comment('作者id'),
            'category_id'=>$this->integer()->notNull()->comment('分类id'),
            'image'=>$this->string()->notNull()->comment('书图片'),
            'intro'=>$this->text()->notNull()->comment('简介'),
            'is_free'=>$this->smallInteger(2)->notNull()->comment('是否收费书'),
            'clicks'=>$this->integer()->comment('观看数'),
            'size'=>$this->integer()->comment('书大小,单位:KB'),
            'type'=>$this->char()->notNull()->comment('文本类型'),
            'score'=>$this->decimal(1,0)->comment('评分'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('book');
    }
}
