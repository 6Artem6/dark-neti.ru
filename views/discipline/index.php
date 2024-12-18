<?php

/**
 * @var app\models\search\DisciplineSearch $searchModel
 * @var yii\web\View $this
 */

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap5\{Html, LinkPager};
use yii\helpers\{Url};

use app\models\helpers\ModelHelper;

use app\widgets\discipline\DisciplineRecord;
use app\widgets\bar\RightBar;

use app\assets\actions\DisciplineIndexAsset;

if (Yii::$app->request->get('tour') == 1) {
	DisciplineIndexAsset::register($this);
}


$this->title = Yii::t('app', 'Все предметы');
?>

<div class="col-sm-12 col-md-8 vstack gap-3">
	<div class="card">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body">
			<div id="div-form" class="row py-1">
				<?php $form = ActiveForm::begin([
					'method' => 'GET',
					'action' => [''],
					'id' => 'discipline-searh-form',
					'successCssClass' => '',
					'class' => ['container', 'px-3'],
				]) ?>
					<div class="row row-cols-1 row-cols-md-2 px-2">
						<div class="col px-1">
							<?= $form->field($searchModel, 'text', [
								'addon' => ModelHelper::getTextClearButton($searchModel, 'text')
							])
							->textInput([
								'onchange' => "$(this).closest('form').submit()",
								'placeholder' => Yii::t('app', 'Поиск предметов'),
								'class' => ['form-control', 'form-control-sm', 'form-field'],
							])->label(false) ?>
						</div>
						<div class="col px-1">
							<?= $form->field($searchModel, 'sort')->widget(Select2::class, [
								'data' => $searchModel->getSortList(),
								'options' => [
									'id' => 'sort',
									'onchange' => "$(this).closest('form').submit()",
									'prompt' => Yii::t('app', 'Выберите сортировку'),
									'class' => ['form-control', 'form-control-sm', 'form-field'],
								],
								'pluginOptions' => [
									// 'allowClear' => true
								],
								'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'sort'),
								'size' => Select2::SMALL
							])->label(false) ?>
						</div>
					</div>
				<?php $form->end() ?>
				<div class="container">
					<div class="row row-cols-1 row-cols-md-2 px-3">
						<div id="div-follows" class="col px-1 py-2">
							<?php if (Yii::$app->request->get('follows')): ?>
								<?= Html::a(Yii::t('app', 'Не показывать подписки'),
									Url::current(['follows' => null]),
									['class' => ['btn', 'btn-sm', 'btn-info', 'w-100', 'rounded-pill']]
								) ?>
							<?php else: ?>
								<?= Html::a(Yii::t('app', 'Показать подписки'),
									Url::current(['follows' => true]),
									['class' => ['btn', 'btn-sm', 'btn-info', 'w-100', 'rounded-pill']]
								) ?>
							<?php endif ?>
						</div>
						<div id="div-current_semestr" class="col px-1  py-2">
							<?php if (Yii::$app->request->get('current_semestr')): ?>
								<?= Html::a(Yii::t('app', 'Не показывать за текущий семестр'),
									Url::current(['current_semestr' => null]),
									['class' => ['btn', 'btn-sm', 'btn-info', 'w-100', 'rounded-pill']]
								) ?>
							<?php else: ?>
								<?= Html::a(Yii::t('app', 'Показать за текущий семестр'),
									Url::current(['current_semestr' => true]),
									['class' => ['btn', 'btn-sm', 'btn-info', 'w-100', 'rounded-pill']]
								) ?>
							<?php endif ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="h-100">
		<?php if ($pages->totalCount): ?>
			<div class="row h5 ms-3 pt-3">
				<?= Yii::t('app', 'Найдено записей: {count}', ['count' => $pages->totalCount]) ?>
			</div>
			<div class="row row-cols-1 row-cols-sm-3 row-cols-md-2 row-cols-lg-2 row-cols-xl-3 g-2 py-4">
				<?php foreach ($provider->getModels() as $record): ?>
					<?= DisciplineRecord::widget([
						'model' => $record
					]) ?>
				<?php endforeach ?>
			</div>

			<?php if ($pages->totalCount > $pages->limit): ?>
				<div class="card card-body mt-3">
					<div class="row">
						<?= LinkPager::widget([
							'pagination' => $pages,
							'registerLinkTags' => true,
							'maxButtonCount' => 5
						]) ?>
					</div>
				</div>
			<?php endif ?>
		<?php else: ?>
			<div class="row justify-content-center row-cols-1 gap-2 py-3">
				<div class="col text-center h3">
						<?= Yii::t('app', "Предметы не найдены.") ?>
				</div>
				<div class="col text-center h4">
					<?= Yii::t('app', "Попробуйте изменить параметры поиска.") ?>
				</div>
				<div class="col text-center h5">
					<hr class="my-4">
					<?= Yii::t('app', "Или задайте вопрос с нужным предметом!") ?>
				</div>
				<div class="col text-center mt-4 h5">
					<?= Html::a(Yii::t('app', 'Задать вопрос'),
						['/question/create'],
						['class' => ['btn', 'btn-outline-primary', 'btn-md', 'text-center', 'w-50']]
					) ?>
				</div>
			</div>
		<?php endif ?>
	</div>
</div>

<?= RightBar::widget([
	'show_popular_questions' => true,
	'show_popular_disciplines' => true,
]) ?>
