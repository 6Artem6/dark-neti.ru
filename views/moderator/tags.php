<?php

/**
 * @var yii\web\View $this
 */

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap5\{Html, LinkPager};
use yii\helpers\Url;

use app\models\data\RecordList;
use app\models\helpers\{ModelHelper};

$this->title = Yii::t('app', 'Список тегов');
?>

<div class="col-12 vstack gap-3">
	<div class="card">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body py-2">
			<div class="row px-3 py-3" id="div-form">
				<?php $form = new ActiveForm([
					'method' => 'GET',
					'action' => [''],
					'id' => 'searh-form-tag',
					'successCssClass' => '',
					'options' => ['class' => ['container']],
				]) ?>
					<div class="row my-2">
						<div class="col-sm-12 col-md-2">
							<?= Html::a(
								Yii::t('app', 'Добавить тег'),
								['tag-create'],
								['class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill', 'ms-2']]
							) ?>
						</div>
						<?php if ($searchModel->parent): ?>
							<div class="col-sm-12 col-md-4">
								<?= Yii::t('app', 'Подтеги тега:') ?>
								<span class="fst-italic">
									<?= $searchModel->parent->tag_name ?>
								</span>
								<?= Html::a(
									Yii::t('app', 'Поиск по всем тегам'),
									Url::current([$searchModel->formName() => ['parent_id' => null]]),
									['class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill', 'ms-2']]
								) ?>
							</div>
						<?php endif ?>
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-6">
							<?= $form->field($searchModel, 'text', [
								'addon' => ModelHelper::getTextClearButton($searchModel, 'text')
							])
							->textInput([
								'onchange' => "$(this).closest('form').submit()",
								'placeholder' => Yii::t('app', 'Поиск записей'),
								'class' => ['form-control', 'form-control-sm', 'form-field'],
							]) ?>
						</div>
						<div class="col-sm-12 col-md-6">
							<?= $form->field($searchModel, 'discipline_name')->widget(Select2::class, [
								'data' => RecordList::getDisciplineQuestionList(),
								'options' => [
									'class' => ['form-control', 'form-control-sm', 'form-field'],
									'onchange' => "$(this).closest('form').submit()",
									'prompt' => Yii::t('app', 'Выберите дисциплину'),
								],
								'pluginOptions' => [
									// 'allowClear' => true
								],
								'addon' => ModelHelper::getSelect2ClearButton($searchModel, 'discipline_name'),
							]) ?>
						</div>
					</div>
				<?= $form->run() ?>
			</div>
		</div>
	</div>

	<div class="card h-100">
		<?php if ($pages->totalCount): ?>
			<div class="card-body py-2">
				<div class="row">
					<?php foreach ($provider->getModels() as $record): ?>
						<?php $children_count = $record->getChildrenCount() ?>
						<div class="col-12">
							<div class="my-2">
								<?= Yii::t('app', 'Название:') ?>
								<span class="fst-italic">
									<?= $record->tag_name ?>
								</span>
							</div>
							<div class="my-2">
								<?= Yii::t('app', 'Дисциплина:') ?>
								<span class="fst-italic">
									<?php if (!empty($record->discipline_id)): ?>
										<?= Html::a($record->discipline->name,
											$record->discipline->getPageLink(),
											['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
									<?php else: ?>
										<?= Html::tag('span', Yii::t('app', 'Все'),
											['class' => ['bg-light', 'rounded', 'text-secondary', 'p-1']]) ?>
									<?php endif ?>
								</span>
							</div>
							<div class="my-2">
								<?= Yii::t('app', 'Подтегов:') ?>
								<span class="fst-italic">
									<?= $children_count ?: Yii::t('app', 'нет') ?>
								</span>
							</div>
							<hr class="my-2">
							<div class="my-2">
								<?= Html::a(Yii::t('app', 'Исправить'),
									$record->getEditLink(),
									['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill']]
								) ?>
								<?php if ($children_count): ?>
									<?= Html::a(Yii::t('app', 'Просмотреть подтеги'),
										Url::current([$searchModel->formName() => ['parent_id' => $record->tag_id]]),
										['class' => ['btn', 'btn-sm', 'btn-info', 'rounded-pill', 'ms-2']]
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
