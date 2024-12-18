<?php

/** @var yii\web\View $this */

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap5\Html;

use app\models\helpers\{ModelHelper, HtmlHelper};

$this->title = Yii::t('app', 'Связь с поддержкой');
?>

<div class="container">
	<div class="row justify-content-center align-items-center align-items-center vh-100">
		<div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-7 col-xxl-7">
			<div class="card">
				<div class="card-body text-center p-4 p-sm-5">
					<h1 class="mb-2"><?= Html::encode($this->title) ?></h1>

					<?php $form = ActiveForm::begin([
						'id' => 'support-form',
						'successCssClass' => '',
						'validateOnBlur' => false,
						'options' => [
							'class' => ['mt-sm-4']
						],
						'fieldConfig' => [
							'template' => "{label}\n{input}\n{error}",
							'labelOptions' => ['class' => 'col-form-label mr-lg-3'],
							'inputOptions' => ['class' => 'form-control'],
							'errorOptions' => ['class' => 'invalid-feedback'],
						],
					]) ?>
						<div class="mb-3 input-group-md">
							<?= $form->field($model, 'type_id')->widget(Select2::class, [
								'data' => $model->typeModel->list,
								'options'=>[
									'prompt' => Yii::t('app', 'Выберите тип')
								],
								'pluginOptions' => [
									// 'allowClear' => true
								],
								'addon' => ModelHelper::getSelect2ClearButton($model, 'type_id'),
							]) ?>
						</div>

						<?php if ($model->isScenarioGuest()): ?>
							<div class="mb-3 input-group-md">
								<?= $form->field($model, 'response_email')->textInput([]) ?>
							</div>
						<?php endif ?>

						<div class="mb-3 input-group-md">
							<?= $form->field($model, 'support_text')->textarea(['rows' => 6]) ?>
						</div>

						<div class="d-grid">
							<?= Html::submitButton(Yii::t('app', 'Отправить'), ['class' => ['btn', 'btn-outline-primary', 'rounded-pill']]) ?>
						</div>
					<?php $form->end() ?>

					<p class="mb-0 mt-3">
						<?= HtmlHelper::getFooterText() ?>
					</p>
					<p class="mb-0 mt-3">
						<?= Html::a(Yii::t('app', 'Назад ко входу'), ['/site/login'], ['class' => ['nav-link', 'ps-0']]) ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
