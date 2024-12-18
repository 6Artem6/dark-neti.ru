<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class UserSettingsAsset extends BaseAsset
{

	public $sourcePath = '@app/assets/actions_assets/user_settings';

	public $css = [
	];

	public $js = [
		'js/tour.js',
	];

	public $depends = [
		\yii\web\YiiAsset::class,
	];
}
