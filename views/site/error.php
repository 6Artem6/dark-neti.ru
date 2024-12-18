<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\bootstrap5\Html;
use yii\web\HttpException;

use app\models\request\SupportType;

$is_http = ($exception instanceof HttpException);
$this->title = ($is_http ? $name : Yii::t('app', 'Ошибка!'));
?>
<div class="site-error">
	<div class="d-flex align-items-center justify-content-center vh-100">
		<div class="text-center">
			<h3 class="display-1 fw-bold">
				<?= Html::encode($this->title) ?>
			</h3>
			<div class="w-100 text-center py-3">
				<img src="/assistant/assistant_25.svg" width="200"/>
			</div>
			<h5 class="d-none">
				<?= nl2br(Html::encode($message)) ?>
			</h5>
			<?php if (!$is_http): ?>
				<?php $url = ['/user/support', 'id' => SupportType::TYPE_ERROR,
					'text' => Yii::t('app', 'Возникла ошибка по ссылке: {url}', [
						'url' => Yii::$app->request->url
					])
				] ?>
				<h5>
					<?= Yii::t('app', 'Ой, что-то пошло не так...') ?>
				</h5>
				<h5>
					<?= Yii::t('app', 'Скорее всего, мы уже в курсе о возникшей проблеме и уже работаем над её устранением.') ?>
				</h5>
				<h5>
					<?= Yii::t('app', 'Если ошибка возникнет через некоторое время снова, пожалуйста, сообщи нам об этом.') ?>
				</h5>
				<div class="mt-3">
					<?= Html::a(Yii::t('app', 'Сообщить'), $url, ['target' => '_blank',
						'class' => ['btn', 'btn-info', 'btn-lg', 'rounded-pill']
					]) ?>
				</div>
			<?php else: ?>
				<h5 class="d-none">
					<?php if ($exception->statusCode == 404): ?>
						<?= Yii::t('app', 'Как ты тут оказался?') ?>
					<?php elseif ($exception->statusCode == 405): ?>
						<?= Yii::t('app', 'Зачем ты Здесь?') ?>
						<?php elseif ($exception->statusCode == 429): ?>
						<?= Yii::t('app', 'Чего ты пытаешься добиться Этими действиями?') ?>
					<?php elseif ($exception->statusCode == 400): ?>
						<?= Yii::t('app', 'Зачем ты это сделал?') ?>
					<?php else: ?>
						<?= Yii::t('app', 'Как ты это сделал?') ?>
					<?php endif ?>
				</h5>
			<?php endif ?>
			<?php if (Yii::$app->request->referrer): ?>
				<div class="mt-3">
					<?= Html::a(Yii::t('app', 'Назад'),
						Yii::$app->request->referrer,
						['class' => ['btn', 'btn-info', 'btn-lg', 'rounded-pill']]
					) ?>
				</div>
			<?php endif ?>
			<div class="mt-3">
				<?= Html::a(Yii::t('app', 'На главную'),
					['/'],
					['class' => ['btn', 'btn-info', 'btn-lg', 'rounded-pill']]
				) ?>
			</div>
		</div>
	</div>
</div>
