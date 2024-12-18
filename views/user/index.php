<?php

/**
 * @var app\models\search\UserSearch $searchModel
 * @var yii\web\View $this
 */

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap5\{Html, LinkPager};
use yii\helpers\{Url};

use app\models\helpers\ModelHelper;

use app\widgets\user\UserRecord;
use app\widgets\bar\RightBar;

$this->title = Yii::t('app', 'Все пользователи');
?>

<div class="col-sm-12 col-md-8 vstack gap-3">
	<div class="card">
		<div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between pb-0">
			<h1 class="h3 card-title"><?= $this->title ?></h1>
		</div>
		<div class="card-body">
			<div id="div-form" class="row py-1">
				<?php $form = ActiveForm::begin([
					'method' => 'GET',
					'action' => [''],
					'id' => 'user-searh-form',
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
								'placeholder' => Yii::t('app', 'Поиск пользователей'),
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
			<div class="row row-cols-2 row-cols-sm-3 row-cols-md-2 row-cols-lg-2 row-cols-xl-3 g-2 py-4">
				<?php foreach ($provider->getModels() as $record): ?>
					<?= UserRecord::widget([
						'model' => $record,
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
				<div class="col h3 text-center">
					<?= Yii::t('app', "Пользователи не найдены.") ?>
				</div>
				<div class="col h4 text-center">
					<?= Yii::t('app', "Попробуйте изменить параметры поиска.") ?>
				</div>
			</div>
		<?php endif ?>
	</div>
</div>

<?= RightBar::widget([
	'show_active_users' => true,
]) ?>
