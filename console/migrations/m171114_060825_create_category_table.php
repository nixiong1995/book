<?php

use yii\db\Migration;

/**
 * Handles the creation of table `category`.
 */
class m171114_060825_create_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('category', [
            'id' => $this->primaryKey(),
            'name'=>$this->char(10)->notNull()->comment('名称'),
            'intro'=>$this->text()->notNull()->comment('简介'),
            'type'=>$this->smallInteger()->notNull()->comment('1:男频;0:女频'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('category');
    }
}
