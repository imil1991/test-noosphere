<?php
use yii\helpers\Html;

/* @var $this yii\web\View */

?>
<div class="site-index">
    <div class="jumbotron">
        <h2>Добрый день, <?= Yii::$app->user->identity->username ?></h2>

        <p>
            <?= Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Logout (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-md btn-success']
            )
            . Html::endForm()
            ?>
        </p>
    </div>
</div>
