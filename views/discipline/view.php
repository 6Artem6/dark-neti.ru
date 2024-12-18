<?php

use app\widgets\discipline\ProfileRecord;
?>

<?= ProfileRecord::widget([
	'model' => $model,
	'tab' => Yii::$app->request->get('tab')
]) ?>
