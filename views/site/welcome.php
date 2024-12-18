<?php

/**
 * @var yii\web\View $this
 */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'DARK-NETi');
?>

<section class="overflow-hidden head-section"
	style="background: radial-gradient(116.18% 118% at 50% 100%, rgba(99, 102, 241, 0.1) 0%, rgba(218, 70, 239, 0.05) 41.83%, rgba(241, 244, 253, 0.07) 82.52%);">
	<div class="container pt-3 pt-sm-4 pt-xl-5">
		<div class="row pt-md-2 pt-lg-5">
			<div class="col-12 d-flex flex-column text-center text-sm-start mt-md-4 pt-5 pb-3 pb-sm-4 py-md-5">
				<h1 class="display-5 mb-1"><?= Yii::t('app', 'Присоединяйся') ?></h1>
				<h1 class="display-5 mb-1"><?= Yii::t('app', 'К Тайному') ?></h1>
				<h1 class="display-5 mb-1"><?= Yii::t('app', 'Сообществу студентов') ?></h1>
				<h1 class="display-5 mb-1"><?= Yii::t('app', 'DARK-NETi') ?></h1>
				<div class="position-relative d-inline-flex align-items-center justify-content-center justify-content-md-start mt-auto pt-3 pt-md-5 pb-5 pb-xl-2">
					<a href="#why" class="btn btn-icon btn-light bg-white stretched-link rounded-circle me-3" data-scroll data-scroll-offset="120">
						<i class="bx bx-chevron-down"></i>
					</a>
					<span class="fs-sm"><?= Yii::t('app', 'Узнать больше') ?></span>
				</div>
			</div>
		</div>
	</div>
</section>

<section id="why" class="container py-4 mt-md-2 mt-lg-4">
	<div class="container mt-3 pt-md-2 pt-lg-4 pb-2 pb-md-4 pb-lg-5">
		<h4 class="h4 text-center pb-2 pb-md-0 mb-4 mb-md-5">
			<?= Yii::t('app', 'Почему мы?') ?>
		</h4>
		<h2 class="h2 text-center pb-2 pb-md-0 mb-4 mb-md-5">
			<?= Yii::t('app', 'В DARK-NETi можно получить помощь по предмету любого направления') ?>
		</h2>
		<h6 class="text-muted text-center pb-2 pb-md-0 mb-2 mb-md-3">
			<?= Yii::t('app', 'DARK-NETi - это платформа для помощи в обучении студентов в любой сфере учёбы путем сотрудничества с другими студентами') ?>
		</h4>
	</div>
	<div class="swiper pt-2 mx-n2"
		data-swiper-options='{
			"slidesPerView": 2,
			"pagination": {
				"el": ".swiper-pagination",
				"clickable": true
			},
			"breakpoints": {
				"500": {
					"slidesPerView": 3,
					"spaceBetween": 8
				},
				"650": {
					"slidesPerView": 4,
					"spaceBetween": 8
				},
				"900": {
					"slidesPerView": 5,
					"spaceBetween": 8
				},
				"1100": {
					"slidesPerView": 6,
					"spaceBetween": 8
				}
			}
		}'
	>
		<div class="swiper-wrapper">
			<div class="swiper-slide py-3">
				<p class="card card-body card-hover text-center px-2 mx-2">
					<?= Yii::t('app', 'Матанализ') ?>
				</p>
			</div>
			<div class="swiper-slide py-3">
				<p class="card card-body card-hover text-center px-2 mx-2">
					<?= Yii::t('app', 'Физика') ?>
				</p>
			</div>
			<div class="swiper-slide py-3">
				<p class="card card-body card-hover text-center px-2 mx-2">
					<?= Yii::t('app', 'Правоведение') ?>
				</p>
			</div>
			<div class="swiper-slide py-3">
				<p class="card card-body card-hover text-center px-2 mx-2">
					<?= Yii::t('app', 'Экономика') ?>
				</p>
			</div>
			<div class="swiper-slide py-3">
				<p class="card card-body card-hover text-center px-2 mx-2">
					<?= Yii::t('app', 'Электротехника') ?>
				</p>
			</div>
			<div class="swiper-slide py-3">
				<p class="card card-body card-hover text-center px-2 mx-2">
					<?= Yii::t('app', 'Химия') ?>
				</p>
			</div>
			<div class="swiper-slide py-3">
				<p class="card card-body card-hover text-center px-2 mx-2">
					<?= Yii::t('app', 'Информатика') ?>
				</p>
			</div>
			<div class="swiper-slide py-3">
				<p class="card card-body card-hover text-center px-2 mx-2">
					<?= Yii::t('app', 'Другие предметы') ?>
				</p>
			</div>
		</div>

		<div class="swiper-pagination position-relative pt-2 mt-4"></div>
	</div>
</section>

<section class="bg-secondary">
	<div class="container pt-3 pt-xl-4 pb-3 pb-xl-5 mt-n2 mt-sm-0 mb-2 mb-md-4 mb-lg-5">
		<div class="row pb-3">
			<div class="offset-md-1 col-md-10 offset-lg-2 col-lg-8">
				<h2 class="h1 text-center my-4"><?= Yii::t('app', 'На чём основано сообщество?') ?></h2>
				<p class="fs-lg text-muted text-center mb-4 mb-xl-5">
					<?= Yii::t('app', 'Главным принципом сообщества является взаимопомщь студентов по учёбе') ?>
				</p>
				<div class="row row-cols-1 row-cols-sm-2 pt-2 pt-sm-3 pt-xl-2">
					<div class="col pb-2 pb-xl-0 mb-4 mb-xl-5">
						<div class="d-flex align-items-start ps-xl-3">
							<div class="d-table bg-secondary rounded-3 flex-shrink-0 p-3 mb-3">
								<i class="bx bx-mask d-block fs-2 text-info"></i>
							</div>
							<div class="ps-4 ps-sm-3 ps-md-4">
								<h3 class="h5 pb-1 mb-2"><?= Yii::t('app', 'Анонимность') ?></h3>
								<p class="mb-0"><?= Yii::t('app', 'Взаимодействие в сообществе происходит без использования Ваших личных данных') ?></p>
							</div>
						</div>
					</div>

					<div class="col pb-2 pb-xl-0 mb-4 mb-xl-5">
						<div class="d-flex align-items-start pe-xl-3">
							<div class="d-table bg-secondary rounded-3 flex-shrink-0 p-3 mb-3">
								<i class="bx bx-message-rounded-dots d-block fs-2 text-info"></i>
							</div>
							<div class="ps-4 ps-sm-3 ps-md-4">
								<h3 class="h5 pb-1 mb-2"><?= Yii::t('app', 'Поддержка 24/7') ?></h3>
								<p class="mb-0"><?= Yii::t('app', 'Мы всегда на связи и реагируем на любые возникающие проблемы') ?></p>
							</div>
						</div>
					</div>

					<div class="col pb-2 pb-xl-0 mb-4 mb-xl-5">
						<div class="d-flex align-items-start ps-xl-3">
							<div class="d-table bg-secondary rounded-3 flex-shrink-0 p-3 mb-3">
								<i class="bx bx-smile d-block fs-2 text-info"></i>
							</div>
							<div class="ps-4 ps-sm-3 ps-md-4">
								<h3 class="h5 pb-1 mb-2"><?= Yii::t('app', 'Удобство') ?></h3>
								<p class="mb-0"><?= Yii::t('app', 'Платформа направлена на упрощение взаимодействия студентов') ?></p>
							</div>
						</div>
					</div>

					<div class="col pb-2 pb-xl-0 mb-4 mb-xl-5">
						<div class="d-flex align-items-start pe-xl-3">
							<div class="d-table bg-secondary rounded-3 flex-shrink-0 p-3 mb-3">
								<i class="bx bx-wrench d-block fs-2 text-info"></i>
							</div>
							<div class="ps-4 ps-sm-3 ps-md-4">
								<h3 class="h5 pb-1 mb-2"><?= Yii::t('app', 'Постоянное улучшение') ?></h3>
								<p class="mb-0"><?= Yii::t('app', 'Мы стараемся развивать проект и Вы можете нам в этом помочь') ?></p>
							</div>
						</div>
					</div>
				</div>
				<div class="row row-cols-1 pt-2 pt-sm-3 pt-xl-2">
					<div class="col">
						<a href="/site/register" class="btn btn-info rounded-pill">
							<?= Yii::t('app', 'Вступить') ?>
							<i class="bx bx-right-arrow-alt fs-xl ms-2 me-n1"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="position-relative pb-5">
	<div class="container pt-md-2 pt-lg-4 pb-2 pb-md-4 pb-lg-5">
		<h2 class="h1 text-center pb-2 pb-md-0 mb-4 mb-md-5"><?= Yii::t('app', 'Как это работает?') ?></h2>
		<div class="position-relative mx-5">
			<button type="button" id="prev-screen" class="btn btn-prev btn-icon position-absolute top-50 start-0 ms-n5 translate-middle-y">
				<i class="bx bx-chevron-left"></i>
			</button>
			<button type="button" id="next-screen" class="btn btn-next btn-icon position-absolute top-50 end-0 me-n5 translate-middle-y">
				<i class="bx bx-chevron-right"></i>
			</button>

			<div class="position-absolute top-0 start-50 translate-middle-x h-100 w-100 w-md-33 zindex-5">
				<div class="d-flex bg-repeat-0 bg-size-cover w-100 h-100 mx-auto"></div>
			</div>

			<div class="position-absolute top-0 start-50 translate-middle-x h-100 w-100 w-md-33">
				<div class="d-flex bg-repeat-0 bg-size-cover w-100 h-100 mx-auto"></div>
			</div>

			<div class="swiper mobile-app-slider"
				data-swiper-options='{
					"slidesPerView": 1,
					"centeredSlides": true,
					"loop": true,
					"tabs": true,
					"pagination": {
						"el": "#swiper-progress",
						"type": "progressbar"
					},
					"navigation": {
						"prevEl": "#prev-screen",
						"nextEl": "#next-screen"
					},
					"breakpoints": {
						"768": { "slidesPerView": 1 }
					}
				}'
			>
				<div class="swiper-wrapper">
					<div class="swiper-slide" data-swiper-tab="#text-1">
						<h3 class="h4 pb-1 mb-2 px-5 text-center"><?= Yii::t('app', 'Шаг 1. Задай вопрос') ?></h3>
						<p class="mb-0 px-5 text-center"><?= Yii::t('app', 'Любой участник может задать вопрос') ?></p>
					</div>

					<div class="swiper-slide" data-swiper-tab="#text-2">
						<h3 class="h4 pb-1 mb-2 px-5 text-center"><?= Yii::t('app', 'Шаг 2. Получи ответ') ?></h3>
						<p class="mb-0 px-5 text-center"><?= Yii::t('app', 'Любой участник может дать ответ') ?></p>
					</div>

					<div class="swiper-slide" data-swiper-tab="#text-3">
						<h3 class="h4 pb-1 mb-2 px-5 text-center"><?= Yii::t('app', 'Шаг 3. Помогай остальным') ?></h3>
						<p class="mb-0 px-5 text-center"><?= Yii::t('app', 'Помогая другим участникам, Вы развиваете сообщество') ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="swiper-progress" class="swiper-pagination bottom-0" style="top: auto;"></div>
</section>

<section id="join"></section>

<section class="py-2 py-lg-5"
	style="background: radial-gradient(116.18% 118% at 50% 100%, rgba(99, 102, 241, 0.1) 0%, rgba(218, 70, 239, 0.05) 41.83%, rgba(241, 244, 253, 0.07) 82.52%);">
	<div class="container mt-3 pt-md-2 pt-lg-4 pb-2">
		<h2 class="h1 text-center pb-2 pb-md-0 mb-4 mb-md-5"><?= Yii::t('app', 'Готовы присоединиться?') ?></h2>
		<h3 class="h3 text-center pb-2 pb-md-0">
			<a href="/site/register" class="btn btn-info btn-lg rounded-pill">
				<?= Yii::t('app', 'Зарегистрироваться') ?>
				<i class="bx bx-right-arrow-alt fs-xl ms-2 me-n1"></i>
			</a>
		</h3>
	</div>
</section>

<section class="bg-secondary py-3">
	<div class="container">
		<div class="row py-2 py-md-4 py-lg-5">
			<div class="col-xl-4 col-md-5 text-center text-md-start pt-md-2 pb-2 pb-md-0 mb-4 mb-md-0">
				<h2 class="pb-2 mb-1 mb-lg-2">
					<?= Yii::t('app', 'Остались вопросы?') ?> <br class="d-none d-md-inline" />
				</h2>
				<h6 class="text-muted pb-3 mb-1 mb-lg-3">
					<?= Yii::t('app', 'Просмотрите частые вопросы') ?>
				</h6>
				<div class="row row-cols-1 row-cols-md-2 g-3 g-sm-4">
					<div class="col">
						<div class="card card-hover">
							<?= Html::beginTag('a', [
								'href' => Url::to(['/site/support']),
								'class' => ['card-body', 'btn-link', 'pb-3', 'px-3']
							]) ?>
								<i class="bx bx-message-rounded-dots d-block fs-2 text-success mb-2 py-1"></i>
								<p class="fs-sm mb-0"><?= Yii::t('app', 'Нужно связаться с нами?') ?></p>
								<p class="text-success text-start px-0">
									<?= Yii::t('app', 'Напишите') ?>&nbsp;<br class="d-none d-md-inline"><?= Yii::t('app', 'в поддержку') ?>
									<i class="bx bx-right-arrow-alt fs-xl ms-2"></i>
								</p>
							<?= Html::endTag('a') ?>
						</div>
					</div>
					<div class="col">
						<div class="card card-hover">
							<?= Html::beginTag('a', [
								'href' => $full_link,
								'target' => '_blank',
								'class' => ['card-body', 'btn-link', 'pb-3', 'px-3']
							]) ?>
								<i class="bx bxl-telegram d-block fs-2 text-info mb-2 py-1"></i>
								<p class="fs-sm mb-0"><?= Yii::t('app', 'Нужно срочно связаться?') ?></p>
								<p class="text-info text-start px-0">
									<?= Yii::t('app', 'Напишите') ?>&nbsp;<br class="d-none d-md-inline"><?= Yii::t('app', 'в Telegram') ?>
									<i class="bx bx-right-arrow-alt fs-xl ms-2"></i>
								</p>
							<?= Html::endTag('a') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-7 offset-xl-1">
				<div class="accordion" id="faq">
					<div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
						<h2 class="accordion-header" id="q1-heading">
							<button class="accordion-button shadow-none rounded-3" type="button"
								data-bs-toggle="collapse" data-bs-target="#q1" aria-expanded="true" aria-controls="q1">
								<?= Yii::t('app', 'Видны ли кому-нибудь личные данные, которые я указываю?') ?>
							</button>
						</h2>
						<div id="q1" class="accordion-collapse collapse show" aria-labelledby="q1-heading" data-bs-parent="#faq">
							<div class="accordion-body fs-sm pt-0">
								<p>
									<?= Yii::t('app', 'Нет. Взаимодействие на платформе происходит абсолютно анонимно.') ?>
								</p>
							</div>
						</div>
					</div>
					<div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
						<h2 class="accordion-header" id="q2-heading">
							<button class="accordion-button shadow-none rounded-3 collapsed" type="button"
								data-bs-toggle="collapse" data-bs-target="#q2" aria-expanded="false" aria-controls="q2">
								<?= Yii::t('app', 'Могу ли я зарегистрироваться?') ?>
							</button>
						</h2>
						<div id="q2" class="accordion-collapse collapse" aria-labelledby="q2-heading" data-bs-parent="#faq">
							<div class="accordion-body fs-sm pt-0">
								<p>
									<?= Yii::t('app', 'Данная платформа на данный момент предназначена исключительно для действующих студентов НГТУ.') ?>
								</p>
							</div>
						</div>
					</div>
					<div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
						<h2 class="accordion-header" id="q3-heading">
							<button class="accordion-button shadow-none rounded-3 collapsed" type="button"
								data-bs-toggle="collapse" data-bs-target="#q3" aria-expanded="false" aria-controls="q3">
								<?= Yii::t('app', 'Могу ли я повторно зарегистрироваться?') ?>
							</button>
						</h2>
						<div id="q3" class="accordion-collapse collapse" aria-labelledby="q3-heading" data-bs-parent="#faq">
							<div class="accordion-body fs-sm pt-0">
								<p class="mb-0">
									<?= Yii::t('app', 'У каждого студента есть возможность зарегистрироваться только один раз.') ?>
								</p>
								<p>
									<?= Yii::t('app', 'Не упустите её.') ?>
								</p>
							</div>
						</div>
					</div>
					<div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
						<h2 class="accordion-header" id="q4-heading">
							<button class="accordion-button shadow-none rounded-3 collapsed" type="button"
								data-bs-toggle="collapse" data-bs-target="#q4" aria-expanded="false" aria-controls="q4">
								<?= Yii::t('app', 'Что делать, если забыл пароль?') ?>
							</button>
						</h2>
						<div id="q4" class="accordion-collapse collapse" aria-labelledby="q4-heading" data-bs-parent="#faq">
							<div class="accordion-body fs-sm pt-0">
								<p>
									<?= Yii::t('app', 'Свяжитесь с нашей поддержкой. Они помогут восстановить Вам доступ.') ?>
								</p>
							</div>
						</div>
					</div>
					<div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
						<h2 class="accordion-header" id="q5-heading">
							<button class="accordion-button shadow-none rounded-3 collapsed" type="button"
								data-bs-toggle="collapse" data-bs-target="#q5" aria-expanded="false" aria-controls="q5">
								<?= Yii::t('app', 'Могу ли я задать мой собственный пароль?') ?>
							</button>
						</h2>
						<div id="q5" class="accordion-collapse collapse" aria-labelledby="q5-heading" data-bs-parent="#faq">
							<div class="accordion-body fs-sm pt-0">
								<p>
									<?= Yii::t('app', 'Пароли создаются автоматически.') ?>
								</p>
							</div>
						</div>
					</div>
					<div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
						<h2 class="accordion-header" id="q6-heading">
							<button class="accordion-button shadow-none rounded-3 collapsed" type="button"
								data-bs-toggle="collapse" data-bs-target="#q6" aria-expanded="false" aria-controls="q6">
								<?= Yii::t('app', 'Могу ли я как-то повлиять на проект?') ?>
							</button>
						</h2>
						<div id="q6" class="accordion-collapse collapse" aria-labelledby="q6-heading" data-bs-parent="#faq">
							<div class="accordion-body fs-sm pt-0">
								<p>
									<?= Yii::t('app', 'Конечно. Мы всегда готовы обсудить любые Ваши предложения.') ?>
								</p>
							</div>
						</div>
					</div>
					<div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
						<h2 class="accordion-header" id="q7-heading">
							<button class="accordion-button shadow-none rounded-3 collapsed" type="button"
								data-bs-toggle="collapse" data-bs-target="#q7" aria-expanded="false" aria-controls="q7">
								<?= Yii::t('app', 'Что я получу, помогая другим?') ?>
							</button>
						</h2>
						<div id="q7" class="accordion-collapse collapse" aria-labelledby="q7-heading" data-bs-parent="#faq">
							<div class="accordion-body fs-sm pt-0">
								<p class="mb-0">
									<?= Yii::t('app', 'Каждый студент может дать и получить самое ценное в учёбе - знания.') ?>
								</p>
								<p>
									<?= Yii::t('app', 'Самые активные участники не останутся незамеченными.') ?>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
