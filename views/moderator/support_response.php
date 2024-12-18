<?php

use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap5\{Html, LinkPager};
use yii\helpers\Url;

use app\models\helpers\ModelHelper;

use app\widgets\support\SupportText;

$this->title = Yii::t('app', 'Ответ на обращение');
?>

<div class="col-sm-12 col-md-8 mb-2 px-0">
	<div class="card p-2">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body">
			<div class="d-grid">
				<?= $model->support_text ?>
			</div>
			<?php $form = ActiveForm::begin([
				'id' => 'support-form',
				'options' => [
					'class' => ['mt-sm-4']
				],
				'fieldConfig' => [
					'template' => "{label}\n{input}\n{error}",
					'labelOptions' => ['class' => 'col-form-label mr-lg-3'],
					'inputOptions' => ['class' => 'form-control'],
					'errorOptions' => ['class' => 'invalid-feedback'],
				],
			]) ?>
				<div class="mb-3 input-group-md">
					<?= $form->field($model, 'response_text')->textarea(['rows' => 6]) ?>
				</div>
				<div class="d-grid">
					<?= Html::submitButton(Yii::t('app', 'Отправить'), ['class' => ['btn', 'btn-outline-primary', 'rounded-pill']]) ?>
				</div>
			<?php $form->end() ?>
			<hr class="my-2">
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
