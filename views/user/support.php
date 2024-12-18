<?php

/** @var yii\web\View $this */

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap5\Html;

use app\models\helpers\ModelHelper;

$this->title = Yii::t('app', 'Написать в поддержку');
?>

<div class="col-sm-12 col-md-8 mb-2 px-0">
	<div class="card p-2">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body text-center">
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
					<?= $form->field($model, 'type_id')->widget(Select2::class, [
						'data' => $model->typeModel->list,
						'options'=>[
							'prompt' => Yii::t('app', 'Выберите тип')
						],
						'pluginOptions' => [
							// 'allowClear' => true
						],
						'addon' => ModelHelper::getSelect2ClearButton($model, 'type_id'),
					]) ?>
				</div>
				<div class="mb-3 input-group-md">
					<?= $form->field($model, 'support_text')->textarea(['rows' => 6]) ?>
				</div>
				<div class="d-grid">
					<?= Html::submitButton(Yii::t('app', 'Отправить'),
						['class' => ['btn', 'btn-outline-primary', 'save-btn', 'rounded-pill']]
					) ?>
				</div>
			<?php $form->end() ?>
		</div>
		<div class="card-body">
			<?php if ($list) { ?>
				<hr class="my-2">
				<div class="h4"><?= Yii::t('app', 'Мои обращения в поддержку') ?>:</div>
				<div class="table-responsive">
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th scope="col"><?= Yii::t('app', 'Дата') ?></th>
								<th scope="col"><?= Yii::t('app', 'Номер') ?></th>
								<th scope="col"><?= Yii::t('app', 'Тема') ?></th>
								<th scope="col"><?= Yii::t('app', 'Статус') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($list as $record){ ?>
								<tr>
									<td><?= $record->timeFull ?></th>
									<td><?= $record->support_id ?></td>
									<td><?= Html::a($record->shortText, $record->getRecordLink()) ?></td>
									<td><?= $record->status->status_name ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } else { ?>
				<h5 class="mt-0">
					<?= Yii::t('app', 'Обращений пока нет.') ?>
				</h5>
				<h5 class="mt-0">
					<?= Html::a(Yii::t('app', 'Написать обращение'),
						['/site/support'],
						['class' => ['btn', 'btn-primary', 'save-btn', 'rounded-pill']]
					) ?>
				</h5>
			<?php } ?>
		</div>
	</div>
</div>
