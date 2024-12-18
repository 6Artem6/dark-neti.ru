<?php

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use kartik\form\ActiveForm;
use yii\bootstrap5\Html;

use app\models\request\SupportType;
use app\models\helpers\HtmlHelper;

$title = Yii::t('app', 'Вход');
$this->title = Yii::t('app', 'Вход в DARK-NETi');
?>
<div class="container">
	<div class="row justify-content-center align-items-center vh-100 py-0 px-0 mx-0">
		<div class="col-sm-10 col-md-8 col-lg-7 col-xl-6 col-xxl-5">
			<div class="card text-center p-2 p-sm-3">
				<div class="card-header">
					<h1 class="mb-2"><?= Html::encode($title) ?></h1>
					<p class="mb-0">
						<?= Yii::t('app', 'Нет аккаунта?') ?> <?= Html::a(Yii::t('app', 'Зарегистрироваться'), ['register']) ?>
					</p>
					<p class="mb-0">
						<?= Yii::t('app', 'Забыли пароль или логин?') ?> <?= Html::a(Yii::t('app', 'Написать в поддержку'), ['support', 'id' => SupportType::TYPE_LOGIN]) ?>
					</p>
				</div>
				<div class="card-body">
					<?php $form = ActiveForm::begin([
						'id' => 'login-form',
						'successCssClass' => '',
						'validateOnBlur' => false,
						'validateOnChange' => false,
						// 'layout' => 'horizontal',
						'fieldConfig' => [
							'template' => "{label}\n{input}\n{error}",
							'labelOptions' => ['class' => 'col-form-label mr-lg-3'],
							'inputOptions' => ['class' => 'form-control'],
							'errorOptions' => ['class' => 'invalid-feedback'],
						],
					]) ?>
						<div class="mb-3 input-group-lg">
							<?= $form->field($model, 'username')->textInput([]) ?>
						</div>
						<div class="mb-3 input-group-lg">
							<?= $form->field($model, 'password')->passwordInput() ?>
						</div>
						<div class="mb-3 pt-3 input-group-lg">
							<?= $form->field($model, 'rememberMe')->checkbox([
								'template' => Html::tag('div', '{input} {label}', ['class' => ['col-lg-8']]) . "\n" .
									Html::tag('div', '{error}', ['class' => ['col-lg-8']]),
							]) ?>
						</div>
						<div class="mt-4 d-grid">
							<?= Html::submitButton(Yii::t('app', 'Войти'), [
								'class' => ['btn', 'btn-md', 'btn-primary', 'login-btn']
							]) ?>
						</div>
					<?php $form->end() ?>
				</div>
				<div class="card-footer">
					<div class="mb-0 mt-1">
						<?= HtmlHelper::getFooterText() ?>
					</div>
					<div class="mb-0 mt-1" id="support-div">
						<?= Html::a(Yii::t('app', 'Поддержка'),
							['/site/support'],
							['id' => 'support-button', 'class' => ['nav-link', 'ps-0']]
						) ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
