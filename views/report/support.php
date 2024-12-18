<?php

/**
 * @var yii\web\View $this
 */

use kartik\form\ActiveForm;
use yii\bootstrap5\{Html, LinkPager};

use app\models\helpers\ModelHelper;

$this->title = $searchModel->getTypeName();
?>

<div class="col-12 vstack gap-3">
	<div class="card h-100">
		<div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
			<h1 class="h4 card-title"><?= $this->title ?></h1>
		</div>

		<div class="col-sm-8 col-md-6 px-2">
			<div class="btn-group w-100" role="group" id="search-buttons">
				<?= Html::a(Yii::t('app', 'Поддержка'), ['report/support' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
						($searchModel->isSupport ? 'active' : '')
					]
				]) ?>
				<?= Html::a(Yii::t('app', 'Вопросы'), ['report/questions' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
						($searchModel->isQuestion ? 'active' : '')
					]
				]) ?>
				<?= Html::a(Yii::t('app', 'Ответы'), ['report/answers' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
						($searchModel->isAnswer ? 'active' : '')
					]
				]) ?>
				<?= Html::a(Yii::t('app', 'Комментарии'), ['report/comments' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
						($searchModel->isComment ? 'active' : '')
					]
				]) ?>
			</div>
		</div>

		<div class="row px-3 py-3" id="div-form">
			<?php $form = ActiveForm::begin([
				'method' => 'GET',
				'action' => [''],
				'id' => 'searh-form-user',
				'successCssClass' => '',
				'class' => ['container', 'px-3', 'py-2'],
			]) ?>
				<div class="row">
					<?= $form->field($searchModel, 'text', [
						'addon' => ModelHelper::getTextClearButton($searchModel, 'text')
					])
					->textInput([
						'onchange' => "$(this).closest('form').submit()",
						'placeholder' => Yii::t('app', 'Поиск записей')
					])->label(false) ?>
				</div>
				<div class="row">
					<?= $form->field($searchModel, 'sort', [
						'template' => "{input}\n{label}",
					])->radioList($searchModel->getSortList(), [
						'class' => 'btn-group',
						'id' => 'sort-buttons',
						'item' => function($index, $label, $name, $checked, $value) {
							$return = Html::input('radio', $name, $value, [
								'checked' => ($checked ? true : null),
								'class' => 'btn-check',
								'id' => $value,
								'onchange' => "$(this).closest('form').submit()",
							]);
							$return .= Html::label($label, $value, [
								'class' => ['btn', 'btn-outline-primary', 'd-grid', 'align-content-center'],
								'style' => ['font-size' => '12px'],
							]);
							return $return;
						}
					])->label(false) ?>
				</div>
			<?php $form->end() ?>
		</div>

		<?php if ($pages->totalCount): ?>
			<div class="card-body py-2">
				<div class="row">
					<?php foreach ($provider->getModels() as $model): ?>
						<div class="col-12">
							<?= Yii::t('app', 'Вопрос:') ?>
							<span class="fst-italic">
								<?= $model->support_text ?>
							</span>
						</div>
						<?php if ($model->response_text): ?>
							<div class="col-12">
								<?= Yii::t('app', 'Ответ:') ?>
								<span class="fst-italic">
									<?= $model->response_text ?>
								</span>
							</div>
						<?php endif ?>
						<hr class="my-4 fw-bold">
					<?php endforeach ?>
				</div>
			</div>

			<?php if ($pages->totalCount > $pages->limit): ?>
				<div class="container">
					<div class="row">
						<hr class="my-4">
						<div class="px-3">
							<?= LinkPager::widget([
								'pagination' => $pages,
								'registerLinkTags' => true
							]) ?>
						</div>
					</div>
				</div>
			<?php endif ?>
		<?php else: ?>
			<div class="row justify-content-center row-cols-1 gap-2 py-3">
				<div class="col mt-4">
					<h3 class="text-center">
						<?= Yii::t('app', "Записи не найдены.") ?>
					</h3>
				</div>
				<div class="col">
					<h4 class="text-center">
						<?= Yii::t('app', "Попробуйте изменить параметры поиска.") ?>
					</h4>
				</div>
			</div>
		<?php endif ?>
	</div>
</div>
