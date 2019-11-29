<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%shop_admin}}`.
 */
class m191119_033344_drop_shop_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%shop_admin}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%shop_admin}}', [
            'id' => $this->primaryKey(),
            'user' => $this->string(),
            'password' => $this->integer(11),
            'birthday' => $this->date(),
        ]);
    }
}
