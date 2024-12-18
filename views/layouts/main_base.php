<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\bootstrap5\Html;

use app\assets\MainAsset;

use app\models\helpers\{HtmlHelper, UserHelper};

use app\widgets\alert\register\FlashAlerts;

MainAsset::register($this);
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

		<meta name="description" content="DARK-NETi - платформа для взаимопомощи студентов в области учёбы.">
		<meta name="keywords" content="DARK-NETi, DARK NETi, Дарк Нети, Дарк Нэти, учёба, обучение, сообщество, взаимопомощь">
		<?= Html::csrfMetaTags() ?>
		<?= HtmlHelper::icons() ?>
		<?php $this->head(); ?>
		<link rel="manifest" href="/manifest.json?v=1.002">
	</head>
	<body>
		<?php $this->beginBody(); ?>
			<main>
				<?= $content ?>
				<?= FlashAlerts::widget() ?>
			</main>
		<?php $this->endBody(); ?>
	</body>
</html>
<?php $this->endPage(); ?>
