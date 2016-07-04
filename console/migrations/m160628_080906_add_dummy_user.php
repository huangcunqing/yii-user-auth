<?php

use yii\db\Migration;
use \api\models\User;

class m160628_080906_add_dummy_user extends Migration
{
    public function up()
    {
        $user = new User();
        $user->email = 'admin@userauth.local';
        $user->password = '123123123';
        $user->generateAuthKey();
        $user->save();
    }

    public function down()
    {
        $user = User::findByEmail('admin@userauth.local');
        if (!empty($user)) {
            $user->delete();
        }
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
