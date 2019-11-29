<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%shop_admin}}`.
 */
class m191119_031735_create_shop_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%shop_admin}}', [
            'id' => $this->primaryKey(),
            'user' => $this->string()->defaultValue('')->unique(),
            'password' => $this->integer(11),
            'birthday' => $this->date(),
        ]);
        $this->addCommentOnTable('{{%shop_admin}}','会员表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%shop_admin}}');
    }
}
