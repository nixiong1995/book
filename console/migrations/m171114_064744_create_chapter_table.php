<?php

use yii\db\Migration;

/**
 * Handles the creation of table `chapter`.
 */
class m171114_064744_create_chapter_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('chapter', [
            'id' => $this->primaryKey(),
            'book_id'=>$this->integer()->notNull()->comment('书id'),
            'no'=>$this->integer()->notNull()->comment('章节号'),
            'chapter_name'=>$this->char(20)->notNull()->comment('章节名称'),
            'path'=>$this->string()->notNull()->comment('文件路径'),
            'is_free'=>$this->smallInteger()->notNull()->comment('是否收费'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('chapter');
    }
}
