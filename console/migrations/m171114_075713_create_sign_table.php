<?php

use yii\db\Migration;

/**
 * Handles the creation of table `sign`.
 */
class m171114_075713_create_sign_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('sign', [
            'id' => $this->primaryKey(),
            'user_id'=>$this->integer()->notNull()->comment('用户id'),
            'time'=>$this->integer()->notNull()->comment('签到时间'),

        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('sign');
    }
}
