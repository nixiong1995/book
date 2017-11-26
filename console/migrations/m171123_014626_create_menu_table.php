<?php

use yii\db\Migration;

/**
 * Handles the creation of table `menu`.
 */
class m171123_014626_create_menu_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('menu', [
            'id' => $this->primaryKey(),
            'name'=>$this->string()->notNull()->comment('菜单名称'),
            'parent_id'=>$this->smallInteger(3)->comment('上级菜单id'),
            'route'=>$this->string()->notNull()->comment('路由'),
            'sort'=>$this->integer()->notNull()->comment('排序'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('menu');
    }
}
