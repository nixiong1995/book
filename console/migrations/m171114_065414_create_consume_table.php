<?php

use yii\db\Migration;

/**
 * Handles the creation of table `consume`.
 */
class m171114_065414_create_consume_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('consume', [
            'id' => $this->primaryKey(),
            'user_id'=>$this->integer()->notNull()->comment('用户id'),
            'consumption'=>$this->decimal(8,2)->notNull()->comment('消费阅票'),
            'deductible'=>$this->decimal(5,2)->notNull()->comment('书券抵扣'),
            'deduction'=>$this->decimal(8,2)->notNull()->comment('实际扣除阅票'),
            'content'=>$this->string()->notNull()->comment('消费内容'),
            'trade_no'=>$this->string()->notNull()->comment('第三方交易号'),
            'create_time'=>$this->integer()->notNull()->comment('消费时间'),

        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('consume');
    }
}
