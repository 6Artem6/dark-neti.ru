<?php

/**
 *  @var yii\web\View $this
 *  @var string $short_link
 *  @var string $full_link
 *  @var string $bot_code
 *  @var bool $has_chat
 **/

use kartik\form\ActiveForm;
use yii\bootstrap5\Html;

use app\models\helpers\HtmlHelper;

use app\assets\actions\UserTelegramAsset;

UserTelegramAsset::register($this);

$this->title = Yii::t('app', 'Код для уведомлений в Телеграме');
?>

<div class="col-sm-12 col-md-8 mb-2">
	<div class="card px-1 px-sm-2 mb-2">
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
						['class' => ['nav-link', 'bi', 'bi-bell-fill', 'bi_icon', 'me-2', 'px-1']]
					) ?>
				</li>
				<li class="nav-item">
					<?= Html::a(HtmlHelper::getIconText(Yii::t('app', 'Активация Telegram-бота')),
						['/user/telegram'],
						[
							'id' => 'telegram-link',
							'class' => ['nav-link', 'bi', 'bi-telegram', 'bi_icon', 'active', 'me-2', 'px-1']
						]
					) ?>
				</li>
			</ul>
		</div>
	</div>

	<div class="card px-1 px-sm-2">
		<div class="card-body">
			<div class="h5">
				<p>
					<?= Yii::t('app', 'Для активации уведомлений в Телеграме необходимо:') ?>
				</p>
				<ul style="list-style: decimal;">
					<li>
						<?= Yii::t('app', 'сгенерировать код') ?>
					</li>
					<li>
						<?= Yii::t('app', 'перейти в диалог с ботом по ссылке: {link}', [
							'link' => Html::a($short_link, $full_link, [
								'target' => '_blank',
								'class' => 'link-primary'
							])
						]) ?>
					</li>
					<li>
						<?= Yii::t('app', 'вставить код') ?>
					</li>
					<?php if ($has_chat): ?>
						<li>
							<span class="text-success">
								<?= Yii::t('app', 'ожидать уведомлений') ?>
							</span>
						</li>
					<?php endif ?>
				</ul>
			</div>
		</div>
		<div class="card-footer">
			<?php $form = ActiveForm::begin([
				'id' => 'telegram-form',
				'successCssClass' => ''
			]) ?>
				<div class="mb-3 input-group-md">
					<?= Html::textInput('code', $bot_code, ['class' => ['form-control'], 'readonly' => true]) ?>
				</div>
				<div class="d-grid">
					<?php if (empty($bot_code)): ?>
						<?= Html::submitButton(Yii::t('app', 'Сгенерировать код'), [
							'class' => ['btn', 'btn-md', 'btn-primary', 'save-btn', 'rounded-pill'],
							'name' => 'generate',
						]) ?>
					<?php else: ?>
						<?= Html::button(Yii::t('app', 'Сгенерировать новый код'), [
							'class' => ['btn', 'btn-md', 'btn-primary', 'save-btn', 'rounded-pill'],
							'data' => [
								'bs-toggle' => "modal",
								'bs-target' => "#generateModal"
							]
						]) ?>
					<?php endif ?>
				</div>
			<?php $form->end() ?>
		</div>
	</div>
</div>

<?php if (!empty($bot_code)): ?>
	<div class="modal fade" id="generateModal" tabindex="-1" role="dialog" aria-labelledby="generateModalTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-danger h2" id="generateModalTitle">
						<?= Yii::t('app', 'Внимание!') ?>
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body h3 text-center">
					<?= Yii::t('app', 'При повторной генераций кода данные о Вашем чате будут утеряны и его потребуется указать повторно!') ?>
				</div>
				<div class="modal-footer">
					<?php $form = ActiveForm::begin([
						'id' => 'telegram-form',
						'options' => [
							'class' => ['mt-sm-4']
						],
					]) ?>
					<?= Html::submitButton(Yii::t('app', 'Сгенерировать новый код'), [
						'class' => ['btn', 'btn-primary'],
						'name' => 'generate',
					]) ?>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Yii::t('app', 'Закрыть') ?></button>
					<?php $form->end() ?>
				</div>
			</div>
		</div>
	</div>
<?php endif ?>

<?php
$this->registerJs(<<<JAVASCRIPT
	$("input[name='code']").click(function() {
		$(this).select();
	});
JAVASCRIPT);
?>
