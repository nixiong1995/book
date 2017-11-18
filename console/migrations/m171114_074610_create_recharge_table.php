<?php

use yii\db\Migration;

/**
 * Handles the creation of table `recharge`.
 */
class m171114_074610_create_recharge_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('recharge', [
            'id' => $this->primaryKey(),
            'user_id'=>$this->integer()->notNull()->comment('用户id'),
            'money'=>$this->decimal(8,2)->notNull()->comment('充值金额'),
            'ticket'=>$this->decimal(8,2)->notNull()->comment('所得阅票'),
            'voucher'=>$this->decimal(5,2)->comment('所得书券'),
            'trade_no'=>$this->string()->notNull()->comment('第三方交易号'),
            'mode'=>$this->char(20)->notNull()->comment('充值方式'),
            'create_time'=>$this->integer()->notNull()->comment('充值时间'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('recharge');
    }
}
