<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

$data = array (
    "grant_type"=>"password",
    "username"=>"admin@userauth.local",
    "password"=>"123123123",
    "client_id"=>"testclient",
    "client_secret"=>"testpass"
);
$ch = curl_init ();
// print_r($ch);
curl_setopt ( $ch, CURLOPT_URL, "http://api.userauth.local/oauth2/token" );
curl_setopt ( $ch, CURLOPT_POST, 1 );
curl_setopt ( $ch, CURLOPT_HEADER, 0 );
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
$return = curl_exec ( $ch );
curl_close ( $ch );

print_r($return);
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to login:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'rememberMe')->checkbox() ?>

            <div class="form-group">
                <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-5">
            <form method="post" action="http://gitlab.xmisp.com/api/v3/session">

                <input type="text" name="login" id="login"/>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <div class="form-group">
                <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>
            </form>
        </div>
    </div>
</div>
<script>
    var username   = document.getElementById("loginform-username");
    var password   = document.getElementById("loginform-password");
    var btn         = document.getElementsByName("login-button")[0];
    username.value ="";
    password.value ="";


</script>