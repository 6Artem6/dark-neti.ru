<?php

/**
 * @var app\models\search\QuestionSearch $searchModel
 * @var yii\web\View $this
 */

use kartik\date\DatePicker;
use kartik\select2\Select2;
use kartik\form\ActiveForm;
use yii\bootstrap5\{Html, LinkPager};
use yii\helpers\Url;
use yii\web\JsExpression;

use app\assets\actions\QuestionIndexAsset;

use app\models\data\RecordList;
use app\models\helpers\{HtmlHelper, ModelHelper};

use app\widgets\discipline\DisciplineSlider;
use app\widgets\teacher\TeacherSlider;
use app\widgets\question\FeedRecord;
use app\widgets\bar\RightBar;


QuestionIndexAsset::register($this);

$at_username = Yii::$app->user->identity->getAtUsername();
$formName = $searchModel->formName();
$record_type = $searchModel->record_type;
$discipline_name = null;
if (!empty($searchModel->discipline_name)) {
	$discipline_name = $searchModel->discipline_name;
} elseif (!empty($searchModel->discipline_list) and (count($searchModel->discipline_list) == 1)) {
	$discipline_name = current($searchModel->discipline_list)->discipline_name;
}

$this->title = Yii::t('app', 'Вопросы');
?>

<?php $form = new ActiveForm([
	'method' => 'GET',
	'action' => [''],
	'id' => 'searh-form-question',
	'successCssClass' => ''
]) ?>
<?= $form->run() ?>

<div class='col-sm-12 col-md-8 vstack gap-2'>
	<div id='search-fields' class='row px-3'>
		<div class='container card px-3 py-2'>
			<div class='card-header px-0 py-2'>
				<div id='search-buttons' class='btn-group w-100'>
					<?php if (!$searchModel->isEmpty()) { ?>
						<?= Html::a(Yii::t('app', 'Убрать фильтры'), [ Url::base() ],
							['class' => ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'd-grid', 'align-content-center']]) ?>
					<?php } ?>

					<?php if ($searchModel->text == $at_username): ?>
						<?= Html::a(Yii::t('app', 'Мои вопросы'), [ Url::current( [ $formName => ['text' => null] ] ) ],
							['class' => ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'd-grid', 'align-content-center',
								'bi', 'bi-check-circle-fill', 'bi_icon']]) ?>
					<?php else: ?>
						<?= Html::a(Yii::t('app', 'Мои вопросы'),
							Url::current( [ $formName => ['text' => $at_username] ] ),
							['class' => ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'd-grid', 'align-content-center']]) ?>
					<?php endif ?>

					<?php if ($searchModel->follow): ?>
						<?= Html::a(Yii::t('app', 'Подписки'), Url::current( [ $formName => ['follow' => null] ] ),
							['class' => ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'd-grid', 'align-content-center',
								'bi', 'bi-check-circle-fill', 'bi_icon']]) ?>
					<?php else: ?>
						<?= Html::a(Yii::t('app', 'Подписки'), Url::current( [ $formName => ['follow' => 1] ] ),
							['class' => ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'd-grid', 'align-content-center']]) ?>
					<?php endif ?>

					<?php if ($searchModel->new_list) { ?>
						<?php if ($searchModel->new): ?>
							<?= Html::a(Yii::t('app', 'Новые события'), Url::current( [ $formName => ['new' => null] ] ),
								['class' => ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'd-grid', 'align-content-center',
									'bi', 'bi-check-circle-fill', 'bi_icon']]) ?>
						<?php else: ?>
							<?= Html::a(Yii::t('app', 'Новые события'), Url::current( [ $formName => ['new' => 1] ] ),
								['class' => ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'd-grid', 'align-content-center']]) ?>
						<?php endif ?>
					<?php } ?>

					<?= Html::button(Yii::t('app', 'Расширенный поиск'), [
						'id' => 'extraDivButton',
						'class' => ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'd-grid', 'align-content-center'],
						'aria-expanded' => 'false',
						'aria-controls' => 'extraDiv',
						'data' => [
							'bs-toggle' => 'collapse',
							'bs-target' => '#extraDiv',
						]
					]) ?>
				</div>
			</div>

			<div class='card-body p-0'>
				<div class='row row-cols-1 row-cols-md-2 pt-2'>
					<div class='col'>
						<?= $form->field($searchModel, 'text', [
							'addon' => ModelHelper::getTextClearButton($searchModel, 'text')
						])
						->textInput([
							'onchange' => 'search()',
							'class' => ['form-control', 'form-control-sm', 'form-field'],
							'placeholder' => Yii::t('app', 'Найти по тексту'),
						]) ?>
					</div>
					<div class='col'>
						<?= $form->field($searchModel, 'discipline_name')->widget(Select2::class, [
							'data' => RecordList::getDisciplineQuestionList($searchModel->discipline_name, true),
							'options' => [
								'id' => 'discipline_name',
								'onchange' => 'search()',
								'class' => ['form-control', 'form-control-sm', 'form-field'],
								// 'placeholder' => Yii::t('app', 'Выберите предмет'),
								// 'prompt' => Yii::t('app', 'Все предметы'),
							],
							'pluginOptions' => [
								// 'allowClear' => true,
							],
							'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'discipline_name'),
							'size' => Select2::SMALL,
						]) ?>
					</div>
				</div>
				<div class='row row-cols-1 row-cols-md-2'>
					<div class='col'>
						<?= $form->field($searchModel, 'faculty_id')->widget(Select2::class, [
							'data' => $searchModel->facultyModel->getList(true),
							'options' => [
								'id' => 'faculty_id',
								'onchange' => 'search()',
								'class' => ['form-control', 'form-control-sm', 'form-field'],
								// 'prompt' => Yii::t('app', 'Выберите Ваш факультет'),
							],
							'pluginOptions' => [
								// 'allowClear' => true
							],
							'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'faculty_id'),
							'size' => Select2::SMALL
						]) ?>
					</div>
					<div class='col'>
						<?= $form->field($searchModel, 'type_id')->widget(Select2::class, [
							'data' => $searchModel->typeModel->getList(true),
							'options' => [
								'id' => 'type_id',
								'onchange' => 'search()',
								'class' => ['form-control', 'form-control-sm', 'form-field'],
								// 'prompt' => Yii::t('app', 'Выберите тип'),
							],
							'pluginOptions' => [
								// 'allowClear' => true
							],
							'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'type_id'),
							'size' => Select2::SMALL
						]) ?>
					</div>
				</div>
			</div>
		</div>

		<div class='container'>
			<div id='extra-div-min' class='row row-cols-1 row-cols-md-2 px-2'>
				<?= Html::beginTag('div', [
					'class' => ['card', 'collapse', 'px-3', 'py-2', 'mt-2', 'mt-md-0', 'mb-md-2'],
					'id' => 'extraDiv',
					'data-is-extra' => ($searchModel->isExtra() ? 'true' : 'false')
				]) ?>
					<div class='row row-cols-1'>
						<div class='col'>
							<?= $form->field($searchModel, 'is_closed')->widget(Select2::class, [
								'data' => $searchModel->getYesNoList(),
								'options' => [
									'id' => 'is_closed',
									'onchange' => 'search()',
									'class' => ['form-control', 'form-control-sm', 'form-field'],
									// 'prompt' => Yii::t('app', 'Выберите тип'),
								],
								'pluginOptions' => [
									// 'allowClear' => true,
									'minimumResultsForSearch' => -1
								],
								'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'is_closed'),
								'size' => Select2::SMALL
							]) ?>
						</div>
						<div class='col'>
							<?= $form->field($searchModel, 'is_answered')->widget(Select2::class, [
								'data' => $searchModel->getYesNoList(),
								'options' => [
									'id' => 'is_answered',
									'onchange' => 'search()',
									'class' => ['form-control', 'form-control-sm', 'form-field'],
									// 'prompt' => Yii::t('app', 'Выберите тип'),
								],
								'pluginOptions' => [
									// 'allowClear' => true,
									'minimumResultsForSearch' => -1
								],
								'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'is_answered'),
								'size' => Select2::SMALL
							]) ?>
						</div>
					</div>
					<div class='row row-cols-1'>
						<div class='col'>
							<?= $form->field($searchModel, 'teacher')->widget(Select2::class, [
								'data' => $searchModel->teacherModel->getListWithQuestionsByName(true),
								'options' => [
									'id' => 'teacher',
									'onchange' => 'search()',
									'class' => ['form-control', 'form-control-sm', 'form-field'],
									// 'placeholder' => Yii::t('app', 'Выберите Ф.И.О. преподавателя'),
								],
								'pluginOptions' => [
									// 'allowClear' => true,
								],
								'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'teacher'),
								'size' => Select2::SMALL
							]) ?>
						</div>
					</div>
					<div class='row row-cols-1 row-cols-sm-2 row-cols-md-1'>
						<div class='col'>
							<?= $form->field($searchModel, 'date_from')->widget(DatePicker::class, [
								'options' => [
									'id' => 'date_from',
									'onchange' => 'search()',
									'class' => ['form-control', 'form-control-sm', 'form-field'],
									// 'prompt' => Yii::t('app', 'Выберите дату от'),
								],
								'pluginOptions' => [
									'locale' => ['format' => 'dd.mm.yyyy'],
									//'format' => 'dd.mm.yyyy',
									'todayHighlight' => true,
									'endDate' => date('d.M.Y')
								],
								'size' => Select2::SMALL
							]) ?>
						</div>
						<div class='col'>
							<?= $form->field($searchModel, 'date_to')->widget(DatePicker::class, [
								'options' => [
									'id' => 'date_to',
									'onchange' => 'search()',
									'class' => ['form-control', 'form-control-sm', 'form-field'],
									// 'prompt' => Yii::t('app', 'Выберите дату до'),
								],
								'pluginOptions' => [
									'locale' => ['format' => 'dd.mm.yyyy'],
									//'format' => 'dd.mm.yyyy',
									'todayHighlight' => true,
									'endDate' => date('d.M.Y')
								],
								'size' => Select2::SMALL
							]) ?>
						</div>
					</div>
					<div class='row row-cols-1 row-cols-sm-2 row-cols-md-1'>
						<div class='col'>
							<?= $form->field($searchModel, 'tag_records')->widget(Select2::class, [
								'data' => RecordList::getTagQuestionListByDiscipline(
									$searchModel->discipline_name,
									$searchModel->tag_records,
									init: true
								),
								'options' => [
									'id' => 'tag_records',
									'onchange' => 'search()',
									'class' => ['form-control', 'form-control-sm', 'form-field'],
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
								'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'tag_records'),
								'size' => Select2::SMALL
							]) ?>
						</div>
						<div class='col'>
							<?= $form->field($searchModel, 'sort')->widget(Select2::class, [
								'data' => $searchModel->getSortList(),
								'options' => [
									'id' => 'sort',
									'onchange' => 'search()',
									'class' => ['form-control', 'form-control-sm', 'form-field'],
									// 'prompt' => Yii::t('app', 'Выберите сортировку'),
								],
								'pluginOptions' => [
									// 'allowClear' => true
								],
								'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'sort'),
								'size' => Select2::SMALL
							]) ?>
						</div>
					</div>
					<?= $form->field($searchModel, 'record_type', ['options' => ['class' => '']])->hiddenInput(['class' => ['form-field']])->label(false) ?>
				<?= Html::endTag('div') ?>
			</div>
		</div>
	</div>

	<div class='row px-3'>
		<div class='card card-body px-1 px-sm-3 py-0'>
			<?= Html::ul($searchModel->getTabs(), [
				'id' => 'question-tabs',
				'class' => ['nav', 'nav-bottom-line', 'border-0', 'align-items-center', 'd-flex', 'justify-content-between', 'mb-0'],
				'item' => function ($item, $type) use ($record_type, $formName) {
					$classes = ['nav-link', 'bi', 'bi_icon', 'me-md-2', 'px-1'];
					if ($type == $record_type) {
						$classes[] = 'active';
						$classes[] = $item['class'].'-fill';
						$link = Url::current([$formName => ['record_type' => null]]);
					} else {
						$classes[] = $item['class'];
						$link = Url::current([$formName => ['record_type' => $type]]);
					}
					$text = Html::a($item['text'], $link, ['class' => $classes]);
					return Html::tag('li', $text, ['class' => ['nav-item']]);
				},
			]) ?>
		</div>
	</div>

	<?php if ($pages->totalCount): ?>
		<div class='row h5 ms-4 pt-3'>
			<?= Yii::t('app', 'Найдено записей: {count}', ['count' => $pages->totalCount]) ?>
		</div>

		<?php $k = 0; ?>
		<?php foreach ($list as $k => $record): ?>
			<?php $k++; ?>
			<div class='row grid gap-2 px-3'>
				<?= FeedRecord::widget([
					'model' => $record,
					'unseen_list' => $searchModel->new_list,
					'report_types' => $report_types,
				]) ?>
			</div>
			<?php if ($k == 1): ?>
				<?php if (!empty($searchModel->question_tag_list)): ?>
					<div class='row grid gap-2 px-3'>
						<div class="card py-3">
							<div class="card-header border-0 text-secondary h5 pt-0 pb-1">
								<?= Yii::t('app', 'Теги') ?>
							</div>
							<div class="card-body p-0">
								<div class='row row-cols-1 row-cols-sm-3 row-cols-md-2 row-cols-lg-2 row-cols-xl-3 grid gap-2 px-3 py-2'>
									<?php foreach ($searchModel->question_tag_list as $tag): ?>
										<div class="col px-1">
											<?= Html::a(
												HtmlHelper::getIconText($tag->name, true) .
												HtmlHelper::getCountText($tag->filter_question_count, true),
												$tag->getRecordLink(),
												['class' => ['badge', 'rounded-pill', 'border', 'border-info', 'bg-light', 'text-secondary', 'w-100']]
											) ?>
										</div>
									<?php endforeach ?>
								</div>
							</div>
						</div>
					</div>
				<?php endif ?>
			<?php elseif ($k == 2): ?>
				<?php if (!empty($searchModel->discipline_list) and (count($searchModel->discipline_list) > 1)): ?>
					<div class='row grid gap-2 px-3 py-2'>
						<div class="card py-3">
							<div class="card-header border-0 text-secondary h5 pt-0 pb-1">
								<?= Yii::t('app', 'Предметы') ?>
							</div>
							<div class="card-body p-0">
								<?= DisciplineSlider::widget([
									'list' => $searchModel->discipline_list,
								]) ?>
							</div>
						</div>
					</div>
				<?php endif ?>
			<?php elseif ($k == 3): ?>
				<?php if (!empty($searchModel->teacher_list) and (count($searchModel->teacher_list) > 1)): ?>
					<div class='row grid gap-2 px-3 py-2'>
						<div class="card py-3">
							<div class="card-header border-0 text-secondary h5 pt-0 pb-1">
								<?= Yii::t('app', 'Преподаватели') ?>
							</div>
							<div class="card-body p-0">
								<?= TeacherSlider::widget([
									'list' => $searchModel->teacher_list,
								]) ?>
							</div>
						</div>
					</div>
				<?php endif ?>
			<?php endif ?>
		<?php endforeach ?>

		<?php if ($k == 0): ?>
			<?php if (!empty($searchModel->question_tag_list)): ?>
				<div class='row grid gap-2 px-3'>
					<div class="card py-3">
						<div class="card-header border-0 text-secondary h5 pt-0 pb-1">
							<?= Yii::t('app', 'Теги') ?>
						</div>
						<div class="card-body p-0">
							<div class='row row-cols-1 row-cols-sm-3 row-cols-md-2 row-cols-lg-2 row-cols-xl-3 grid gap-2 px-3 py-2'>
								<?php foreach ($searchModel->question_tag_list as $tag): ?>
									<div class="col px-1">
										<?= Html::a(
											HtmlHelper::getIconText($tag->name, true) .
											HtmlHelper::getCountText($tag->filter_question_count, true),
											$tag->getRecordLink(),
											['class' => ['badge', 'rounded-pill', 'border', 'border-info', 'bg-light', 'text-secondary', 'w-100']]
										) ?>
									</div>
								<?php endforeach ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif ?>
		<?php endif ?>

		<?php if ($k <= 1): ?>
			<?php if (!empty($searchModel->discipline_list) and (count($searchModel->discipline_list) > 1)): ?>
				<div class='row grid gap-2 px-3 py-2'>
					<div class="card py-3">
						<div class="card-header border-0 text-secondary h5 pt-0 pb-1">
							<?= Yii::t('app', 'Предметы') ?>
						</div>
						<div class="card-body p-0">
							<?= DisciplineSlider::widget([
								'list' => $searchModel->discipline_list,
							]) ?>
						</div>
					</div>
				</div>
			<?php endif ?>
		<?php endif ?>

		<?php if ($k <= 2): ?>
			<?php if (!empty($searchModel->teacher_list) and (count($searchModel->teacher_list) > 1)): ?>
				<div class='row grid gap-2 px-3 py-2'>
					<div class="card py-3">
						<div class="card-header border-0 text-secondary h5 pt-0 pb-1">
							<?= Yii::t('app', 'Преподаватели') ?>
						</div>
						<div class="card-body p-0">
							<?= TeacherSlider::widget([
								'list' => $searchModel->teacher_list,
							]) ?>
						</div>
					</div>
				</div>
			<?php endif ?>
		<?php endif ?>

		<?php if ($pages->totalCount > $pages->limit): ?>
			<div class='row px-3'>
				<div class='card card-body mt-3'>
					<?= LinkPager::widget([
						'pagination' => $pages,
						'registerLinkTags' => true
					]) ?>
				</div>
			</div>
		<?php endif ?>
	<?php else: ?>
		<div class='card justify-content-center mx-3 py-4'>
			<div class='py-2 text-center h3'>
				<?= Yii::t('app', 'Записей не было найдено.') ?>
			</div>
			<div class='py-2 text-center h4'>
				<?= Yii::t('app', 'Попробуйте изменить параметры поиска.') ?>
			</div>
			<div class='py-2 text-center h5'>
				<hr class='my-4'>
				<?= Yii::t('app', 'Или задайте вопрос сами!') ?>
			</div>
			<div class='py-2 text-center mt-4 h5'>
				<?= Html::a(Yii::t('app', 'Задать вопрос'),
					['/question/create'],
					['class' => ['btn', 'btn-outline-primary', 'btn-md', 'text-center', 'w-50']]
				) ?>
			</div>
		</div>
	<?php endif ?>
</div>

<?= RightBar::widget([
	'show_search' => true,
	'show_popular_questions' => true,
	'show_active_users' => true,
	'show_popular_disciplines' => true,
	'show_discipline_best' => true,
	'show_similar_disciplines' => true,
	'discipline_name' => $discipline_name,
]) ?>
