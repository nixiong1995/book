<?php

use yii\db\Migration;

/**
 * Handles the creation of table `author`.
 */
class m171114_060251_create_author_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('author', [
            'id' => $this->primaryKey(),
            'name'=>$this->char(10)->notNull()->comment('作者姓名'),
            'image'=>$this->string()->comment('作者图片'),
            'intro'=>$this->text()->notNull()->comment('简介'),
            'popularity'=>$this->integer()->comment('作者人气'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('author');
    }
}
