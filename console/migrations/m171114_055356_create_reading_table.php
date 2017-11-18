<?php

use yii\db\Migration;

/**
 * Handles the creation of table `reading`.
 */
class m171114_055356_create_reading_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('reading', [
            'id' => $this->primaryKey(),
            'user_id'=>$this->integer()->notNull()->comment('用户id'),
            'book_id'=>$this->integer()->notNull()->comment('书id'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('reading');
    }
}
