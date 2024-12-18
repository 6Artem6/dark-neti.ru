<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

use app\widgets\support\SupportText;

$this->title = Yii::t('app', 'Просмотр обращения');
?>

<div class="col-sm-12 col-md-8 mb-2 px-0">
	<div class="card p-2">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body">
			<h4><?= Yii::t('app', 'Номер обращения - {id}', ['id' => $model->support_id]) ?></h4>
			<h4><?= Yii::t('app', 'Статус - {status}', ['status' => $model->status->status_name]) ?></h4>
			<?= SupportText::widget([
				'model' => $model,
				'is_request' => true,
			]) ?>
			<?php if ($model->isSolved()) { ?>
				<hr class="my-1">
				<?= SupportText::widget([
					'model' => $model,
					'is_request' => false,
				]) ?>
			<?php } ?>
		</div>
	</div>
</div>
