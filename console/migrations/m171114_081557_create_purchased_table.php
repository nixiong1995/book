<?php

use yii\db\Migration;

/**
 * Handles the creation of table `purchased`.
 */
class m171114_081557_create_purchased_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('purchased', [
            'id' => $this->primaryKey(),
            'user_id'=>$this->integer()->notNull()->comment('用户id'),
            'book_id'=>$this->integer()->notNull()->comment('书id'),
            'chapter_no'=>$this->smallInteger(10)->notNull()->comment('章节号'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('purchased');
    }
}
