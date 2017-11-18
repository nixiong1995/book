<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_details`.
 */
class m171113_092435_create_user_details_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('user_details', [
            'id' => $this->primaryKey(),
            'user_id'=>$this->integer()->notNull()->comment('用户id'),
            'address'=>$this->string(10)->notNull()->comment('地区'),
            'vip'=>$this->smallInteger(2)->notNull()->defaultValue(0)->comment('1:vip;0:普通'),
            'birthday'=>$this->char(20)->comment('生日'),
            'sex'=>$this->smallInteger(2)->comment('0:女;1:男'),
            'head'=>$this->string()->comment('头像'),
            'time'=>$this->integer()->comment('阅读累加时长(分钟)'),
            'f_author'=>$this->string()->comment('喜欢的作者'),
            'f_type'=>$this->string()->comment('喜欢书类型'),
            'ticket'=>$this->decimal(8,2)->comment('阅票余额'),
            'voucher'=>$this->decimal(5,2)->comment('书票余额'),

        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('user_details');
    }
}
