<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\bootstrap5\Html;

use app\models\helpers\HtmlHelper;

use app\assets\LandingAsset;

use app\widgets\form\info\{HonorCodeModal, RulesModal};

LandingAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html class="dark-mode" lang="<?= Yii::$app->language ?>">
	<head>
		<title><?= Html::encode($this->title) ?></title>

		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta charset="<?= Yii::$app->charset ?>" />

		<meta name="description" content="DARK-NETi - платформа для взаимопомощи студентов в области учёбы.">
		<meta name="keywords" content="DARK-NETi, DARK NETi, Дарк Нети, Дарк Нэти, учёба, обучение, сообщество, взаимопомощь">
		<meta name="theme-color" content="#000000" />
		<?= Html::csrfMetaTags() ?>
		<?= HtmlHelper::icons() ?>
		<?php $this->head(); ?>
		<link rel="manifest" href="/manifest.json?v=1.002">

		<script>
			let mode = window.localStorage.getItem("mode"),
				root = document.getElementsByTagName("html")[0];
			if ((mode == null) || (mode === "dark")) {
				root.classList.add("dark-mode");
			} else {
				root.classList.remove("dark-mode");
			}
			(function () {
				window.onload = function () {
					const preloader = document.querySelector(".page-loading");
					preloader.classList.remove("active");
					setTimeout(function () {
						preloader.remove();
					}, 1000);
				};
			})();
		</script>
	</head>
	<body>
		<?php $this->beginBody(); ?>
		<div class="page-loading active">
			<div class="page-loading-inner">
				<div class="page-spinner"></div>
				<span>Загрзка...</span>
			</div>
		</div>
			<main class="page-wrapper" style="height: 100vh;">
				<header class="header navbar navbar-expand-lg position-absolute navbar-sticky" >
					<div class="container px-1">
						<a href="#join" class="navbar-brand d-inline py-0 mx-0">
							<img src="/logo/logo.svg" class="me-2 logo logo-lg logo-dark" style="width: 228px;" alt="DARK-NETi" />
							<img src="/logo/logo-dark.svg" class="me-2 logo logo-lg logo-light" style="width: 228px;" alt="DARK-NETi" />

							<img src="/logo/logo-sm.svg" class="me-2 logo logo-sm logo-dark" style="height: 65px;" alt="DARK-NETi" />
							<img src="/logo/logo-sm-dark.svg" class="me-2 logo logo-sm logo-light" style="height: 65px;" alt="DARK-NETi" />
						</a>
						<div id="navbarNav" class="offcanvas offcanvas-start">
							<div class="offcanvas-header border-bottom">
								<a class="w-100 px-2" href="/">
									<img class="logo-text logo-dark" src="/logo/logo.svg" alt="">
									<img class="logo-text logo-light" src="/logo/logo-dark.svg" alt="">
								</a>
							</div>
							<div class="offcanvas-body">
							</div>
							<div class="offcanvas-header border-top">
								<a href="/site/register" class="btn btn-info rounded-pill w-100">
									<i class="bx bx-log-in fs-4 lh-1 me-1"></i>
									&nbsp;Зарегистрироваться
								</a>
							</div>
							<div class="offcanvas-header border-top">
								<a href="/site/login" class="btn btn-info rounded-pill w-100">
									<i class="bx bx-log-in fs-4 lh-1 me-1"></i>
									&nbsp;Войти
								</a>
							</div>
						</div>
						<a href="/site/login" class="btn btn-info btn-sm fs-sm rounded-pill d-inline-flex">
							<i class="bx bx-log-in fs-5 lh-1 me-1"></i>
							&nbsp;Войти
						</a>
					</div>
				</header>

				<?= $content ?>

				<?= HonorCodeModal::widget() ?>
				<?= RulesModal::widget() ?>
				<footer class="footer pt-3 pb-4 pb-lg-5">
					<div class="container text-center pt-3">
						<div class="navbar-brand justify-content-center text-dark m-0">
							<div class="form-switch mode-switch" data-bs-toggle="mode">
								<!-- <img src="/logo/logo.svg" class="logo logo-dark" style="width: 228px;" alt="DARK-NETi" id="logo-dark" /> -->
								<img src="/logo/logo-dark.svg" class="logo" style="width: 228px;" alt="DARK-NETi" id="" />
								<input type="checkbox" class="form-check-input d-none" id="theme-mode" />
							</div>
						</div>
						<ul class="nav justify-content-center pt-3 pb-4 pb-lg-5">
							<li class="nav-item">
								<a href="/site/login" class="nav-link"><?= Yii::t('app', 'Войти') ?></a>
							</li>
							<li class="nav-item">
								<a href="/site/register" class="nav-link"><?= Yii::t('app', 'Зарегистрироваться') ?></a>
							</li>
							<li class="nav-item">
								<a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#honorCodeModal"><?= Yii::t('app', 'Кодекс чести') ?></a>
							</li>
							<li class="nav-item">
								<a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#rulesModal"><?= Yii::t('app', 'Правила сайта') ?></a>
							</li>
							<li class="nav-item">
								<a href="/site/support" class="nav-link"><?= Yii::t('app', 'Связаться') ?></a>
							</li>
						</ul>
						<p class="nav d-block fs-sm text-center pt-3 mt-lg-2 mb-0">
							<span class="opacity-80">&copy; <?= Yii::t('app', 'Все права защищены.') ?></span>
						</p>
					</div>
				</footer>
			</main>

			<a href="#top" class="btn-scroll-top" data-scroll>
				<span class="btn-scroll-top-tooltip text-muted fs-sm me-2"><?= Yii::t('app', 'Наверх') ?></span>
				<i class="btn-scroll-top-icon bx bx-chevron-up"></i>
			</a>
		<?php $this->endBody(); ?>
	</body>
</html>
<?php $this->endPage(); ?>
