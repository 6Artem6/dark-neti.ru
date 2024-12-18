<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class UserTelegramAsset extends BaseAsset
{

	public $sourcePath = '@app/assets/actions_assets/user_telegram';

	public $css = [
	];

	public $js = [
		'js/tour.js',
	];

	public $depends = [
		\yii\web\YiiAsset::class,
	];
}
