<?php

/**
 * @var app\models\Question $model
 * @var yii\web\View $this
 */

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap5\Html;
use yii\helpers\{ArrayHelper, Url};

use app\widgets\question\QuestionRecord;

$this->title = Yii::t('app', 'Блокировка вопроса');
?>

<div class="col-sm-8 col-md-6 vstack gap-3">
	<div class="card">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body">
			<div class="col-12">
				<?= QuestionRecord::widget([
					'model' => $record,
					'is_short' => true,
				]) ?>
			</div>
		</div>

		<div class="card-body">
			<?php $form = ActiveForm::begin([
				'id' => 'question-form',
				'action' => Url::to(['api/save-moderator/limit-question', 'id' => $record->id]),
				'successCssClass' => '',
				'enableAjaxValidation' => true,
				'validationUrl' => Url::to(['api/save-moderator/limit-question', 'id' => $record->id]),
				'ajaxParam' => 'validate',
				'options' => [
					'class' => ['save-form'],
					'enctype' => 'multipart/form-data',
				],
			]) ?>
				<div class="row">
					<div class="col-sm-12 col-md-6">
						<?= $form->field($model, 'question_reason[]')->checkboxList(
							$model->reportTypeModel->getQuestionList()
						) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12 col-md-6">
						<?= $form->field($model, 'time')->widget(Select2::class, [
							'data' => ArrayHelper::map($model->getTimeList(), 'name', 'title'),
							'options'=>[
								'prompt' => Yii::t('app', 'Выберите время ограничения')
							],
							'pluginOptions' => [
								'allowClear' => true
							]
						]) ?>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12 col-md-6">
						<?= Html::submitButton('Сохранить', ['class' => ['btn', 'btn-outline-primary', 'rounded-pill']]) ?>
					</div>
				</div>
			<?php $form->end() ?>
		</div>
	</div>
</div>