<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subscriber}}`.
 */
class m230317_134933_create_subscriber_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%subscriber}}', [
            'id' => $this->primaryKey(),
            'channel_id' => $this->integer(11),
            'user_id' => $this->integer(11),
            'created_at' => $this->integer(11),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%subscriber}}');
    }
}
