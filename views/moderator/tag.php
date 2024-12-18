<?php

/**
 * @var app\models\question\Question $model
 * @var yii\web\View $this
 */

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

use app\models\helpers\ModelHelper;

$this->title = ($model->isNewRecord ? Yii::t('app', 'Создание тега') : Yii::t('app', 'Редактирование тега'));
?>

<?php $form = ActiveForm::begin([
	'id' => 'tag-form',
	'successCssClass' => '',
	'options' => [
		'class' => ['col-sm-8', 'col-md-6', 'vstack', 'gap-3', 'g-3', 'px-2', 'mx-1'],
	],
]) ?>
	<div class="card">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-sm-12 offset-md-3 col-md-6">
					<?= $form->field($model, 'tag_name')->textInput([
						'placeholder' => Yii::t('app', 'Укажите кратко имя тега')
					]) ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 offset-md-3 col-md-6">
					<?= $form->field($model, 'discipline_id')->widget(Select2::class, [
						'data' => $model->disciplineModel->getTagList(true),
						'options' => [
							'id' => 'discipline_id',
							'class' => ['form-control', 'form-control-md', 'form-field'],
							'prompt' => Yii::t('app', 'Выберите дисциплину'),
						],
						'pluginOptions' => [
							// 'allowClear' => true
						],
						'addon' => ModelHelper::getSelect2ClearButton($model, 'discipline_id'),
					]) ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 offset-md-3 col-md-6">
					<?= $form->field($model, 'parent_id')->widget(Select2::class, [
						'data' => $model->getParentList(),
						'options' => [
							'class' => ['form-control', 'form-control-md', 'form-field'],
							'placeholder' => Yii::t('app', 'Выберите родительский тег'),
						],
						'pluginOptions' => [
							'ajax' => [
								'url' => ['/api/moderator/tag'],
								'dataType' => 'json',
								'method' => 'POST',
								'data' => new JsExpression(<<<JAVASCRIPT
									function(params) {
										return {
											q: params.term,
											discipline_id: $('#discipline_id').val()
										};
									}
								JAVASCRIPT)
							],
						],
						'addon' => ModelHelper::getSelect2ClearButton($model, 'parent_id'),
					]) ?>
				</div>
			</div>
		</div>
		<div class="card-footer">
			<div class="col-12 col-md-4 col-lg-3">
				<?= Html::submitButton(
					($model->isNewRecord ? Yii::t('app', 'Сохранить запись') : Yii::t('app', 'Изменить запись')),
					['class' => ['btn', 'btn-lg', 'btn-outline-primary', 'rounded-pill', 'w-100']]
				) ?>
			</div>
		</div>
	</div>
<?php $form->end() ?>
