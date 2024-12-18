<?php

/**
* @var app\models\data\UserData $site_model
* @var yii\web\View $this
*/

use kartik\form\ActiveForm;
use yii\bootstrap5\Html;

use app\models\helpers\HtmlHelper;

use app\assets\actions\UserSettingsAsset;

UserSettingsAsset::register($this);

$this->title = Yii::t('app', 'Настройка уведомлений');
?>

<?php $form = ActiveForm::begin([
	'id' => 'settings-form',
	'options' => [
		'class' => ['col-sm-12', 'col-md-8', 'vstack', 'gap-3']
	],
	'successCssClass' => ''
]) ?>
	<div class="card">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body small h5 mb-0 py-0">
			<ul class="nav nav-bottom-line border-0">
				<li class="nav-item">
					<?= Html::a(HtmlHelper::getIconText(Yii::t('app', 'Уведомления')),
						['/user/settings'],
						['class' => ['nav-link', 'bi', 'bi-bell-fill', 'bi_icon', 'active', 'me-2', 'px-1']]
					) ?>
				</li>
				<li class="nav-item">
					<?= Html::a(HtmlHelper::getIconText(Yii::t('app', 'Активация Telegram-бота')),
						['/user/telegram'],
						[
							'id' => 'telegram-link',
							'class' => ['nav-link', 'bi', 'bi-telegram', 'bi_icon', 'me-2', 'px-1']
						]
					) ?>
				</li>
			</ul>
		</div>
	</div>

	<div class="card px-1 px-sm-2">
		<div class="card-body py-0">
			<?= HtmlHelper::errorSummary([$site_model, $bot_model]) ?>
		</div>
		<div class="card-body small settings-div">
			<div class="row h6">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= Yii::t('app', 'Мои вопросы') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Сайт') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Телеграм') ?>
				</div>
				<hr class="mb-0">
			</div>

			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('my_question_answer') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'my_question_answer')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'my_question_answer')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('my_question_comment') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'my_question_comment')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'my_question_comment')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('my_question_answer_comment') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'my_question_answer_comment')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'my_question_answer_comment')->checkbox()->label(false) ?>
				</div>
			</div>

			<div class="row h6 mt-3">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= Yii::t('app', 'Подписки: вопросы') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Сайт') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Телеграм') ?>
				</div>
				<hr class="mb-0">
			</div>

			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_question_edit') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_question_edit')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_question_edit')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_question_answer') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_question_answer')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_question_answer')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_question_comment') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_question_comment')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_question_comment')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_question_answer_comment') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_question_answer_comment')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_question_answer_comment')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_question_my_answer_comment') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_question_my_answer_comment')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_question_my_answer_comment')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_question_answered') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_question_answered')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_question_answered')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('my_answer_like') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'my_answer_like')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'my_answer_like')->checkbox()->label(false) ?>
				</div>
			</div>

			<div class="row h6 mt-3">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= Yii::t('app', 'Подписки: пользователи') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Сайт') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Телеграм') ?>
				</div>
				<hr class="mb-0">
			</div>

			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_user_question_create') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_user_question_create')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_user_question_create')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_user_question_edit') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_user_question_edit')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_user_question_edit')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_user_answer') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_user_answer')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_user_answer')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_user_comment') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_user_comment')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_user_comment')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_user_question_answered') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_user_question_answered')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_user_question_answered')->checkbox()->label(false) ?>
				</div>
			</div>

			<div class="row h6 mt-3">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= Yii::t('app', 'Подписки: предметы') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Сайт') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Телеграм') ?>
				</div>
				<hr class="mb-0">
			</div>

			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_discipline_question_create') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_discipline_question_create')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_discipline_question_create')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_discipline_question_edit') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_discipline_question_edit')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_discipline_question_edit')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_discipline_question_answer') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_discipline_question_answer')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_discipline_question_answer')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_discipline_question_comment') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_discipline_question_comment')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_discipline_question_comment')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_discipline_question_answer_comment') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_discipline_question_answer_comment')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_discipline_question_answer_comment')->checkbox()->label(false) ?>
				</div>
			</div>
			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('followed_discipline_question_answered') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'followed_discipline_question_answered')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'followed_discipline_question_answered')->checkbox()->label(false) ?>
				</div>
			</div>

			<div class="row h6 mt-3">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= Yii::t('app', 'Изменения') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Сайт') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= Yii::t('app', 'Телеграм') ?>
				</div>
				<hr class="mb-0">
			</div>

			<div class="row border-bottom settings-row mx-0">
				<div class="col-6 col-md-8 px-0 px-sm-2">
					<?= $site_model->getAttributeLabel('answer_edit') ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($site_model, 'answer_edit')->checkbox()->label(false) ?>
				</div>
				<div class="col-3 col-md-2 px-0 px-sm-2 text-center">
					<?= $form->field($bot_model, 'answer_edit')->checkbox()->label(false) ?>
				</div>
			</div>
		</div>

		<div class="card-footer">
			<div class="d-flex justify-content-end flex-wrap">
				<?php if (!$site_model->isDefault() or !$bot_model->isDefault()) { ?>
					<div class="p-2">
						<?= Html::submitButton('По умолчанию', [
							'name' => 'default',
							'class' => ['btn', 'btn-md', 'btn-secondary', 'save-btn', 'rounded-pill']
						]) ?>
					</div>
				<?php } ?>
				<div class="p-2">
					<?= Html::submitButton('Сохранить настройки', [
						'name' => 'save',
						'class' => ['btn', 'btn-md', 'btn-primary', 'save-btn', 'rounded-pill']
					]) ?>
				</div>
			</div>
		</div>
	</div>
<?php $form->end() ?>
