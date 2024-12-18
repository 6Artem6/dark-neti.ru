<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\bootstrap5\Html;

use app\assets\{UserAsset, ModeratorAsset};

use app\models\helpers\{HtmlHelper, UserHelper};

use app\widgets\alert\main\{Alert, FlashAlerts};
use app\widgets\badge\FlashBadges;
use app\widgets\bar\{FooterBar, LeftBar, NavBar};
use app\widgets\form\LogoutModalForm;
use app\widgets\form\info\{HonorCodeModal, RulesModal};

UserAsset::register($this);
if (Yii::$app->user->identity->isModerator()) {
	ModeratorAsset::register($this);
}
$theme = UserHelper::getTheme();
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html data-theme="<?= $theme ?>" lang="<?= Yii::$app->language ?>">
	<head>
		<title><?= Html::encode($this->title) ?></title>

		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<meta charset="<?= Yii::$app->charset ?>" />
		<?= Html::csrfMetaTags() ?>
		<?= HtmlHelper::icons() ?>
		<?php $this->head(); ?>
		<link rel="manifest" href="/manifest.json?v=1.002">
	</head>
	<body>
		<?php $this->beginBody(); ?>
		<?= NavBar::widget() ?>
		<main class="main">
			<div class="container-fluid">
				<?= LeftBar::widget() ?>
				<div class="page-content">
					<div class="row grid gy-2 gy-md-0">
						<?= $content ?>
					</div>
				</div>
			</div>
			<div class="toast-container alerts-div">
				<?= FlashAlerts::widget() ?>
				<?= Alert::widget([
					'id' => 'alertMessage'
				]) ?>
			</div>
		</main>
		<?= FlashBadges::widget() ?>
		<?= HonorCodeModal::widget() ?>
		<?= RulesModal::widget() ?>
		<?= LogoutModalForm::widget() ?>
		<div class="user-info"></div>
		<div class="preloader">
			<div class="preloader-item preloader-img"></div>
		</div>
		<?php $this->endBody(); ?>
	</body>
</html>
<?php $this->endPage(); ?>
