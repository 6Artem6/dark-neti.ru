<?php

/** @var yii\web\View $this */
/** @var app\models\user\Register $model */
/** @var bool $tour */

use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use yii\bootstrap5\Html;

use app\assets\actions\SiteRegisterAsset;

use app\models\helpers\HtmlHelper;

use app\widgets\form\info\{HonorCodeModal, RulesModal};

SiteRegisterAsset::register($this);


$title = Yii::t('app', 'Регистрация');
$this->title = Yii::t('app', 'Регистрация в DARK-NETi');
?>
<div class="container">
	<?php if ($model->hasErrors()): ?>
		<div class="row justify-content-center align-items-center pt-2">
			<div class="col-sm-10 col-md-8 col-lg-7 col-xl-6 col-xxl-5">
				<?= HtmlHelper::errorSummary($model, ['class' => ['alert', 'alert-info']]) ?>
			</div>
		</div>
	<?php endif ?>
	<div class="row justify-content-center align-items-center vh-100 py-0 px-0 mx-0">
		<div class="col-sm-10 col-md-8 col-lg-7 col-xl-6 col-xxl-5">

			<div class="card text-center py-3 p-2 p-sm-3">
				<div class="card-header">
					<h1 class="mb-2"><?= Html::encode($title) ?></h1>
					<p class="mb-0">
						<?= Yii::t('app', 'Уже есть аккаунт?') ?>
						<?= Html::a(Yii::t('app', 'Войти'), ['login']) ?>
					</p>
				</div>
				<div class="card-body px-2">
					<?php $form = ActiveForm::begin([
						'id' => 'register-form',
						'successCssClass' => '',
						// 'validateOnBlur' => false,
						'validateOnChange' => false,
						// 'layout' => 'horizontal',
						'fieldConfig' => [
							'template' => "{label}\n{input}\n{error}",
							'labelOptions' => ['class' => 'col-form-label mr-lg-3'],
							'inputOptions' => ['class' => 'form-control'],
							'errorOptions' => ['class' => 'invalid-feedback'],
						],
					]) ?>
						<ul class="nav nav-pills m-0 small">
							<li class="nav-item mx-auto">
								<button class="nav-link active"
									id="nav-info-tab"
									data-bs-toggle="tab"
									data-bs-target="#nav-info"
									type="button"
									role="tab"
									aria-controls="nav-info"
									aria-selected="true">
									<?= Yii::t('app', 'Шаг 0') ?>
								</button>
							</li>
							<li class="nav-item mx-auto">
								<button class="nav-link disabled"
									id="nav-fio-tab"
									data-bs-toggle="tab"
									data-bs-target="#nav-fio"
									type="button"
									role="tab"
									aria-controls="nav-fio"
									aria-selected="false">
									<?= Yii::t('app', 'Шаг 1') ?>
								</button>
							</li>
							<li class="nav-item mx-auto">
								<button class="nav-link disabled"
									id="nav-group_name-tab"
									data-bs-toggle="tab"
									data-bs-target="#nav-group_name"
									type="button"
									role="tab"
									aria-controls="nav-group_name"
									aria-selected="false">
									<?= Yii::t('app', 'Шаг 2') ?>
								</button>
							</li>
							<li class="nav-item mx-auto">
								<button class="nav-link disabled"
									id="nav-student_email-tab"
									data-bs-toggle="tab"
									data-bs-target="#nav-student_email"
									type="button"
									role="tab"
									aria-controls="nav-student_email"
									aria-selected="false">
									<?= Yii::t('app', 'Шаг 3') ?>
								</button>
							</li>
							<li class="nav-item mx-auto">
								<button class="nav-link disabled"
									id="nav-birth_date-tab"
									data-bs-toggle="tab"
									data-bs-target="#nav-birth_date"
									type="button"
									role="tab"
									aria-controls="nav-birth_date"
									aria-selected="false">
									<?= Yii::t('app', 'Шаг 4') ?>
								</button>
							</li>
							<li class="nav-item mx-auto">
								<button class="nav-link disabled"
									id="nav-register-tab"
									data-bs-toggle="tab"
									data-bs-target="#nav-register"
									type="button"
									role="tab"
									aria-controls="nav-register"
									aria-selected="false">
									<?= Yii::t('app', 'Шаг 5') ?>
								</button>
							</li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane container active" id="nav-info">
								<div class="register-step" id="step-1">
									Привет! Добро пожаловать в DARK-NETi!<br>
									Меня зовут Роберт и я буду твоим ассистентом.<br>
									Для вступления в наше сообщество нам сперва нужно убедиться, что ты являешься студентом.<br>
									Не бойся, все твои данные не попадут в чужие руки. Всё только между нами.<br>
									<div class="w-100 text-center py-3">
										<img src="/assistant/assistant_1.svg" width="200"/>
									</div>

									<div class="btn btn-sm btn-primary rounded-pill"
										onclick="$('#step-1').hide(250); $('#step-2').show(250)">
										Дальше
									</div>
								</div>
								<div class="register-step" id="step-2" style="display: none;">
									Перед регистрацией убедись, что у тебя есть корпоративная почта НГТУ.<br>
									Как это проверить?<br>
									Перейди на официальный сайт университета по ссылке <a href="https://mail2.nstu.ru" target="_blank">mail2.nstu.ru</a> и попробуй авторизоваться!<br>
									<div class="w-100 text-center py-3">
										<img src="/assistant/assistant_2.svg" width="200"/>
									</div>

									<div class="btn btn-sm btn-dark rounded-pill"
										onclick="$('#step-1').show(250); $('#step-2').hide(250)">
										Назад
									</div>
									<div class="btn btn-sm btn-primary rounded-pill"
										onclick="$('#step-2').hide(250); $('#step-3').show(250)">
										Дальше
									</div>
								</div>
								<div class="register-step" id="step-3" style="display: none;">
									Не получилось войти на корпоративную почту? <br>
									Скорее всего, тебе необходимо ее создать. После создания почты подожди 10 минут, а потом возвращайся к нам!<br>
									<div class="w-100 text-center py-3">
										<img src="/assistant/assistant_5.svg" width="200"/>
									</div>

									<div class="btn btn-sm btn-dark rounded-pill"
										onclick="$('#step-2').show(250); $('#step-3').hide(250)">
										Назад
									</div>
									<div class="btn btn-sm btn-primary rounded-pill"
										onclick="$('#step-3').hide(250); $('#step-4').show(250)">
										Дальше
									</div>
								</div>
								<div class="register-step" id="step-4" style="display: none;">
									Обрати внимание, что данные, которые ты вводишь, должны быть такими же, как и в системе университета!<br>
									<div class="w-100 text-center py-3">
										<img src="/assistant/assistant_8.svg" width="200"/>
									</div>

									<div class="btn btn-sm btn-dark rounded-pill"
										onclick="$('#step-3').show(250); $('#step-4').hide(250)">
										Назад
									</div>
									<div class="btn btn-sm btn-primary rounded-pill"
										onclick="$('#nav-fio-tab').removeClass('disabled').trigger('click'); $('#nav-info-tab').addClass('text-success');">
										Дальше
									</div>
								</div>
							</div>
							<div class="tab-pane container" id="nav-fio">
								<div class="mb-3 input-group-lg" id="fio-div">
									<?= $form->field($model, 'fio')->textInput(['id' => 'fio']) ?>
								</div>
								<div class="register-step" id="step-5">
									Итак, давай знакомиться! Как тебя зовут?<br>
									<div class="w-100 text-center py-3">
										<img src="/assistant/assistant_3.svg" width="200"/>
									</div>

									<div class="btn btn-sm btn-dark rounded-pill"
										onclick="$('#nav-info-tab').trigger('click');">
										Назад
									</div>
									<div class="btn btn-sm btn-primary rounded-pill"
										onclick="$('#nav-group_name-tab').trigger('click');">
										Дальше
									</div>
								</div>
							</div>
							<div class="tab-pane container fade" id="nav-group_name">
								<div class="mb-3 input-group-lg" id="group_name-div">
									<?= $form->field($model, 'group_name')->textInput(['id' => 'group_name']) ?>
								</div>
								<div class="register-step" id="step-6">
									А в какой группе учишься?<br>
									<div class="w-100 text-center py-3">
										<img src="/assistant/assistant_4.svg" width="200"/>
									</div>

									<div class="btn btn-sm btn-dark rounded-pill"
										onclick="$('#nav-fio-tab').trigger('click');">
										Назад
									</div>
									<div class="btn btn-sm btn-primary rounded-pill"
										onclick="$('#nav-student_email-tab').trigger('click');">
										Дальше
									</div>
								</div>
							</div>
							<div class="tab-pane container fade" id="nav-student_email">
								<div class="mb-3 input-group-lg" id="student_email-div">
									<?= $form->field($model, 'student_email')->textInput(['id' => 'student_email']) ?>
								</div>
								<div class="register-step" id="step-7">
									Мне нужно, чтобы ты подтвердил аккаунт, поэтому тебе необходимо пройти несколько шагов:<br>
									<ol>
										<li>Укажи свою корпоративную почту</li>
										<li>Перейди на <a href="https://mail2.nstu.ru" target="_blank">mail2.nstu.ru</a></li>
										<li>Авторизуйся</li>
										<li>Там тебя уже будет ждать письмо с подтверждением</li>
										<li>Продолжи регистрацию</li>
									</ol>
									Видишь, ничего сложного!
									<div class="w-100 text-center py-3">
										<img src="/assistant/assistant_2.svg" width="200"/>
									</div>

									<div class="btn btn-sm btn-dark rounded-pill"
										onclick="$('#nav-group_name-tab').trigger('click');">
										Назад
									</div>
									<div class="btn btn-sm btn-primary rounded-pill"
										onclick="$('#nav-birth_date-tab').trigger('click');">
										Дальше
									</div>
								</div>
							</div>
							<div class="tab-pane container fade" id="nav-birth_date">
								<div class="mb-3 input-group-lg" id="birth_date-div">
									<?= $form->field($model, 'birth_date')->widget(DatePicker::class, [
										'options' => [
											'id' => 'birth_date',
											'placeholder' => Yii::t('app', 'Выберите дату')
										],
										'pluginOptions' => [
											'autoclose' => true,
											'locale' => ['format' => 'dd.mm.yyyy'],
											// 'format' => 'dd.mm.yyyy',
											// 'todayHighlight' => true,
											'endDate' => date('d.M.Y', strtotime('-16 years'))
										]
									]) ?>
								</div>
								<div class="mb-3 py-3 input-group-lg" id="agreement-div">
									<?= $form->field($model, 'agreement')->checkbox(['id' => 'agreement', 'checked' => false]) ?>
									<p class="mb-0 mt-4 small">
										<?= Yii::t('app', 'Регистрируясь на сайте, Вы принимаете {honor_code} и {rules} сайта', [
											'honor_code' => Html::a(Yii::t('app', 'кодекс чести'), '#', [
												'class' => ['link-primary'],
												'data-bs-toggle' => "modal",
												'data-bs-target' => "#honorCodeModal"
											]),
											'rules' => Html::a(Yii::t('app', 'правила'), '#', [
												'class' => ['link-primary'],
												'data-bs-toggle' => "modal",
												'data-bs-target' => "#rulesModal"
											]),
										]) ?>
									</p>
								</div>
								<div class="register-step" id="step-8">
									Осталось совсем чуть-чуть!<br>
									Сколько тебе лет? Нужно, чтобы ты указал свою реальную дату рождения.<br>
									Не бойся, никто не узнает о твоем возрасте кроме меня, но это поможет избежать путаницы с другими студентами.<br>
									А так же не забудь поставить галочку, что ты принимаешь наши условия и правила!
									<div class="w-100 text-center py-3">
										<img src="/assistant/assistant_7.svg" width="200"/>
									</div>

									<div class="btn btn-sm btn-dark rounded-pill"
										onclick="$('#nav-student_email-tab').trigger('click');">
										Назад
									</div>
									<div class="btn btn-sm btn-primary rounded-pill"
										onclick="$('#nav-register-tab').trigger('click');">
										Дальше
									</div>
								</div>
							</div>
							<div class="tab-pane container fade" id="nav-register">
								<div class="my-4 d-grid" id="register-div">
									<?= Html::submitButton(Yii::t('app', 'Зарегистрироваться'), [
										'id' => 'register-button',
										'disabled' => true,
										'class' => ['btn', 'btn-md', 'btn-primary', 'register-btn'],
									]) ?>
								</div>
								<div class="register-step" id="step-9">
									Вот и все! Согласись, было не сложно, но я уверен, что твое потраченное время на регистрацию окупится.<br>
									Жми скорее на кнопку!
									<div class="w-100 text-center py-3">
										<img src="/assistant/assistant_10.svg" width="200"/>
									</div>

									<div class="btn btn-sm btn-dark rounded-pill"
										onclick="$('#nav-birth_date-tab').trigger('click');">
										Назад
									</div>
								</div>
							</div>
						</div>
					<?php $form->end() ?>
				</div>
				<div class="card-footer">
					<div class="mb-0 mt-1">
						<?= HtmlHelper::getFooterText() ?>
					</div>
					<div class="mb-0 mt-1" id="support-div">
						<?= Yii::t('app', 'Возникли трудности?') ?>
						<?= Html::a(Yii::t('app', 'Напиши в поддержку'),
							['/site/support'],
							[
								'target' => '_blank',
								'id' => 'support-button',
								'class' => ['link-primary', 'ps-0']
							]
						) ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?= HonorCodeModal::widget() ?>
	<?= RulesModal::widget() ?>
</div>
