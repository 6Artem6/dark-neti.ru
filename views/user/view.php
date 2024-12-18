<?php

use app\widgets\user\ProfileRecord;
?>

<?= ProfileRecord::widget([
	'model' => $model,
	'tab' => Yii::$app->request->get('tab')
]) ?>
