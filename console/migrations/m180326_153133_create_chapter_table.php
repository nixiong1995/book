<?php

use yii\db\Migration;

/**
 * Handles the creation of table `chapter`.
 */
class m180326_153133_create_chapter_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        for($i=0;$i<100;$i++){
            $this->createTable('chapter', [
                'id' => $this->integer()->unique(),
                'book_id'=>$this->integer()->notNull()->comment('书id'),
                'no'=>$this->smallInteger(5)->notNull()->comment('章节号'),
                'chapter_name'=>$this->char(30)->notNull()->comment('章节名称'),
                'path'=>$this->char(50)->notNull()->comment('文件路径'),
                'is_free'=>$this->smallInteger(2)->notNull()->comment('是否收费'),
                'word_count'=>$this->smallInteger(4)->notNull()->comment('字数'),
                'create_time'=>$this->integer()->notNull()->comment('创建时间'),
                'update_time'=>$this->integer()->comment('更新时间'),
                $this->createIndex('book_id','chapter','book_id'),
                $this->createIndex('no','chapter','no'),
            ]);
        }

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('chapter');
    }
}
