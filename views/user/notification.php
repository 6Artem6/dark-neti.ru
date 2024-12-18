<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

use app\widgets\notification\MessageList;

$this->title = Yii::t('app', 'Уведомления');
?>

<div class="col-sm-12 col-md-8 vstack gap-3">
	<div class="card">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body">
			<?php if ($messages): ?>
				<?= MessageList::widget([
					'list' => $messages,
				]) ?>
			<?php else: ?>
				<h5 class="text-center my-5"><?= Yii::t('app', 'Новых уведомлений нет') ?></h5>
			<?php endif ?>
		</div>
	</div>
</div>