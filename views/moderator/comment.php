<?php

/**
 * @var yii\web\View $this
 */

use kartik\form\ActiveForm;
use yii\bootstrap5\{Html, LinkPager};

use app\assets\actions\ModeratorSearchAsset;

use app\models\helpers\{ModelHelper, HtmlHelper};

ModeratorSearchAsset::register($this);

$this->title = $searchModel->getTypeName();
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
					]
				]) ?>
				<?= Html::a(Yii::t('app', 'Вопросы'), ['moderator/question-search' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
						($searchModel->isQuestion ? 'active' : '')
					]
				]) ?>
				<?= Html::a(Yii::t('app', 'Ответы'), ['moderator/answer-search' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
						($searchModel->isAnswer ? 'active' : '')
					]
				]) ?>
				<?= Html::a(Yii::t('app', 'Комментарии'), ['moderator/comment-search' ], [
					"class" => [
						"btn", "btn-outline-info", "btn-sm",
						'w-100', 'd-grid', 'align-content-center',
						($searchModel->isComment ? 'active' : '')
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
			<?php $form->run() ?>
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
									<?= Html::a($record->author->name,
										$record->author->getPageLink(),
										['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
								</span>
							</div>
							<div class="my-2">
								<?php if ($searchModel->isQuestion): ?>
									<?= Yii::t('app', 'Вопрос:') ?>
								<?php elseif ($searchModel->isAnswer): ?>
									<?= Yii::t('app', 'Ответ:') ?>
								<?php elseif ($searchModel->isComment): ?>
									<?= Yii::t('app', 'Комментарий:') ?>
								<?php endif ?>
								<span class="fst-italic">
									<?= Html::a($record->shortText,
										$record->getRecordLink(),
										['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
								</span>
							</div>
							<div class="my-2">
								<?= Html::button(Yii::t('app', 'Заявлений: {count}', ['count' => $record->reportCount]), [
										'class' => ['btn', 'btn-light', 'btn-sm', 'rounded-pill'],
										'data-bs-toggle' => "collapse",
										'role' => "button",
										'aria-expanded' => "false",
										"data-bs-target" => '#' . $id,
										'aria-controls' => $id
									]) ?>
								<?= Html::beginTag('div', ['id' => $id, 'class' => 'collapse']) ?>
									<div class="card card-body mt-2">
										<?php foreach ($record->reportsByType as $reports): ?>
											<?php $type = current($reports)->type ?>
											<div>
												<?= $type->type_name ?> - <?= count($reports) ?>
												<?= HtmlHelper::actionModeratorButton(Yii::t('app', 'Не согласиться'),
													'report-close',
													$reports[0]->report_id, [
														'class' => ['btn', 'btn-sm', 'btn-danger', 'rounded-pill'],
													]) ?>
											</div>
										<?php endforeach ?>
									</div>
								<?= Html::endTag('div') ?>
							</div>
							<hr class="my-2">
							<div class="my-2">
								<?= Html::a(Yii::t('app', 'Исправить'),
									$record->getEditLink(),
									['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary', 'me-2']]
								) ?>
								<?= Html::a(Yii::t('app', 'Зафиксировать нарушение'),
									$record->getLimitLink(),
									['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-danger-soft', 'me-2']]
								) ?>
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
