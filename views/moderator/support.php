<?php

/**
 * @var yii\web\View $this
 */

use kartik\form\ActiveForm;
use yii\bootstrap5\{Html, LinkPager};

use app\assets\actions\ModeratorSearchAsset;

use app\models\helpers\{ModelHelper};

ModeratorSearchAsset::register($this);

$this->title = Yii::t('app', 'Обращения в поддержку');
?>

<div class="col-12 vstack gap-3">
	<div class="card h-100">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="row px-2">
			<div class="btn-group w-100" role="group" id="search-buttons">
				<?= Html::a(Yii::t('app', 'Поддержка'), ['moderator/support-search' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
						'active'
					]
				]) ?>
				<?= Html::a(Yii::t('app', 'Вопросы'), ['moderator/question-search' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
					]
				]) ?>
				<?= Html::a(Yii::t('app', 'Ответы'), ['moderator/answer-search' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
					]
				]) ?>
				<?= Html::a(Yii::t('app', 'Комментарии'), ['moderator/comment-search' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
					]
				]) ?>
			</div>
		</div>

		<div class="row px-3 py-3" id="div-form">
			<?php $form = new ActiveForm([
				'method' => 'GET',
				'action' => [Yii::$app->request->pathInfo, 'type' => $searchModel->type],
				'id' => 'searh-form-user',
				'successCssClass' => '',
				'options' => ['class' => ['container', 'px-3', 'py-2']],
			]) ?>
				<div class="row">
					<?= $form->field($searchModel, 'text', [
						'addon' => ModelHelper::getTextClearButton($searchModel, 'text')
					])
					->textInput([
						'onchange' => "$(this).closest('form').submit()",
						'class' => ['form-control', 'form-field'],
						'placeholder' => Yii::t('app', 'Поиск записей'),
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
			<?= $form->run() ?>
		</div>

		<?php if ($pages->totalCount): ?>
			<div class="card-body py-2">
				<div class="row">
					<?php foreach ($provider->getModels() as $record): ?>
						<?php $id = $searchModel->type.'-'.$record->id ?>
						<div class="col-12">
							<div class="my-2">
								<?= Yii::t('app', 'Автор:') ?>
								<span class="fst-italic">
									<?php if ($record->isGuest()): ?>
										<?= $record->getSenderName() ?>
									<?php else: ?>
										<?= Html::a($record->author->name,
											$record->author->getPageLink(),
											['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
									<?php endif ?>
								</span>
							</div>
							<div class="my-2">
								<?= Yii::t('app', 'Обращение:') ?>
								<span class="fst-italic">
									<?= Html::a($record->shortText,
										$record->getRecordLink(),
										['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
								</span>
							</div>
							<hr class="my-2">
							<div class="my-2">
								<?php if ($record->isSolved()): ?>
									<?= Html::a(Yii::t('app', 'Исправить'),
										$record->getResponseLink(),
										['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill', 'me-2']]
									) ?>
								<?php else: ?>
									<?= Html::a(Yii::t('app', 'Ответить'),
										$record->getResponseLink(),
										['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill', 'me-2']]
									) ?>
								<?php endif ?>
							</div>
						</div>
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
