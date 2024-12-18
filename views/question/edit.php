<?php

/**
 * @var app\models\question\Question $model
 * @var yii\web\View $this
 */

use kartik\datetime\DateTimePicker;
use kartik\select2\Select2;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

use app\components\tuieditor\TuiEditor;
use app\models\data\RecordList;
use app\models\helpers\ModelHelper;

use app\assets\actions\QuestionCreateAsset;

QuestionCreateAsset::register($this);


$this->title = Yii::t('app', 'Редактирование вопроса');
?>

<?php $form = ActiveForm::begin([
	'id' => 'question-form',
	'action' => Url::to(['api/save/edit-question', 'id' => $model->id]),
	'validateOnBlur' => false,
	'successCssClass' => '',
	'enableAjaxValidation' => true,
	'validationUrl' => Url::to(['api/save/edit-question', 'id' => $model->id]),
	'ajaxParam' => 'validate',
	'options' => [
		'class' => ['col-sm-8', 'col-md-6', 'vstack', 'gap-3', 'g-3', 'px-2', 'mx-1', 'save-form'],
		'enctype' => 'multipart/form-data',
	],
]) ?>
	<div class="card">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
			<p class="mb-0 mt-1">
				<?= Html::a(Yii::t('app', 'Нужен совет?'), '#', [
					'id' => 'tour-button',
					'class' => ['link-primary', 'text-secondary', 'text-muted']
				]) ?>
			</p>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					<?= $form->field($model, 'question_title')->textInput([
						'id' => 'question_title',
						'placeholder' => Yii::t('app', 'Укажите кратко суть вопроса'),
					]) ?>
				</div>
				<div class="col-sm-12 col-lg-6">
					<?= $form->field($model, 'discipline_name')->widget(Select2::class, [
						'data' => RecordList::getDisciplineQuestionList($model->discipline_name),
						'options' => [
							'id' => 'discipline_name',
							'class' => ['form-control', 'form-control-md', 'form-field'],
							'placeholder' => Yii::t('app', 'Дисциплина вопроса')
						],
						'pluginOptions' => [
							// 'tags' => true,
							'tokenSeparators' => ['.'],
							'maximumInputLength' => 50,
							'maximumSelectionLength' => 2,
						],
						'addon' => ModelHelper::getSelect2ClearButton($model, 'discipline_name'),
					]) ?>
				</div>
				<div class="col-sm-12 col-lg-6">
					<?= $form->field($model, 'type_id')->widget(Select2::class, [
						'data' => $model->typeModel->getList(),
						'options' => [
							'id' => 'type_id',
							'class' => ['form-control', 'form-control-md', 'form-field'],
							'prompt' => Yii::t('app', 'Выберите тип задания')
						],
						'pluginOptions' => [
							// 'allowClear' => true
						],
						'addon' => ModelHelper::getSelect2ClearButton($model, 'type_id'),
					]) ?>
				</div>
				<div class="col-sm-12 col-lg-6">
					<?= $form->field($model, 'teacher_id')->widget(Select2::class, [
						'data' => RecordList::getTeacherQuestionList(),
						'options' => [
							'id' => 'teacher_id',
							'class' => ['form-control', 'form-control-md', 'form-field'],
							'placeholder' => Yii::t('app', 'Выберите ФИО преподавателя'),
						],
						'pluginOptions' => [
							'ajax' => [
								'url' => ['/api/list/teacher'],
								'dataType' => 'json',
								'delay' => 250,
								'cache' => true,
								'method' => 'POST',
								'data' => new JsExpression(<<<JAVASCRIPT
									function(params) {
										return {
											q: params.term,
											discipline_name: $('#discipline_name').val(),
											faculty_id: $('#faculty_id').val()
										};
									}
								JAVASCRIPT)
							],
						],
						'addon' => ModelHelper::getSelect2ClearButton($model, 'teacher'),
					]) ?>
				</div>
				<div class="col-sm-12 col-lg-6">
					<?= $form->field($model, 'end_datetime')->widget(DateTimePicker::class, [
						'options' => [
							'id' => 'end_datetime',
							'placeholder' => Yii::t('app', 'Крайний срок сдачи работы'),
						],
						'pluginOptions' => [
							'autoclose' => true,
							'format' => 'dd.mm.yyyy hh:ii',
							'startDate' => date('01.01.2022 00:00'),
							'todayHighlight' => true
						],
						'size' => Select2::SMALL,
						// 'type' => DateTimePicker::TYPE_INPUT,
					]) ?>
				</div>
				<div class="col-sm-12">
					<?= $form->field($model, 'tag_list')->widget(Select2::class, [
						'data' => RecordList::getTagQuestionListByDiscipline(
							$model->discipline_name,
							$model->tag_list,
							init: true
						),
						'initValueText' => $model->tag_list,
						'options' => [
							'id' => 'tag_list',
							'class' => ['form-control', 'form-control-md', 'form-field'],
							'placeholder' => Yii::t('app', 'Выберите или впишите подходящие теги'),
							'multiple' => true,
						],
						'pluginOptions' => [
							'tags' => true,
							'tokenSeparators' => [','],
							// 'minimumInputLength' => 2,
							'maximumInputLength' => 50,
							'maximumSelectionLength' => 5,
							'ajax' => [
								'url' => ['/api/list/tag'],
								'dataType' => 'json',
								'delay' => 250,
								'cache' => true,
								'method' => 'POST',
								'data' => new JsExpression(<<<JAVASCRIPT
									function(params) {
										return {
											q: params.term,
											discipline_name: $('#discipline_name').val()
										};
									}
								JAVASCRIPT)
							],
						],
						'addon' => ModelHelper::getSelect2ClearButton($model, 'tag_list'),
					]) ?>
				</div>
				<div class="col-12">
					<?= $form->field($model, 'question_text')->widget(TuiEditor::class, [
						'options' => [
							'id' => 'question_text',
						],
						'pluginOptions' => [
							// 'placeholder' => Yii::t('app', 'Текст Вашего вопроса')
						]
					]) ?>
				</div>

				<hr />

				<?php if (!empty($model->files)): ?>
					<div class="col-12 my-1">
						<?php foreach ($model->files as $file): ?>
							<?php $div_id = 'div-' . $file->file_id ?>
							<?= Html::beginTag('div', [
								'id' => $div_id,
								'class' => ['row', 'border', 'rounded', 'mx-2']
							]) ?>
							<div class="d-flex px-0">
								<div class="p-2 flex-grow-1">
									<?= $file->getDocHtml() ?>
									<?= $form->field($model, 'old_files[]')
										->hiddenInput(['value' => $file->file_id])
										->label(false)
									?>
								</div>
								<div class="p-2 file-name">
									<span class="text-break"><?= $file->file_user_name ?></span>
								</div>
								<div class="p-2">
									<?= Html::button(Yii::t('app', 'Удалить'), [
										'class' => ['btn', 'btn-sm', 'btn-danger', 'save-btn', 'rounded-pill', 'btn-delete', 'px-2'],
										'data-div-id' => $div_id
									]) ?>
								</div>
							</div>
							<?= Html::endTag('div') ?>
						<?php endforeach ?>
					</div>
				<?php endif ?>

				<div class="col-12">
					<?= $form->field($model, 'upload_files[]')->widget(FileInput::class, [
						'options' => [
							'id' => 'upload_files',
							'multiple' => true,
						],
						'pluginOptions' => [
						   'showUpload' => false,
						   'maxFileCount' => 10,
						   'maxFileSize' => 2 * 1024,
						   'allowedFileExtensions' => $model->getExtensionList(),
						   'theme' => 'bs5', // 'bs5', 'fa5', 'gly',
						],
					]) ?>
				</div>
			</div>
		</div>
		<div class="card-footer">
			<div class="col-12 offset-md-8 col-md-4">
				<?= Html::submitButton('Сохранить изменения', [
					'class' => ['btn', 'btn-md', 'btn-primary', 'save-btn', 'rounded-pill', 'w-100']
				]) ?>
			</div>
		</div>
	</div>
<?php $form->end() ?>
