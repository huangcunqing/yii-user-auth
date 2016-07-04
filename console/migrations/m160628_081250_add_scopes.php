<?php

use yii\db\Migration;
use \filsh\yii2\oauth2server\models\OauthScopes;

class m160628_081250_add_scopes extends Migration
{
    public function up()
    {
        $scopes = [
            ['scope' => 'default', 'is_default' => 1],
            ['scope' => 'custom', 'is_default' => 0],
            ['scope' => 'protected', 'is_default' => 0],
        ];
        foreach ($scopes as $scope) {
            $so = new OauthScopes();
            $so->attributes = $scope;
            $so->save();
        }
    }

    public function down()
    {
        $this->truncateTable(OauthScopes::tableName());
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
