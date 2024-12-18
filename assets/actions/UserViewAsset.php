<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class UserViewAsset extends BaseAsset
{

	public $publishOptions = ['forceCopy' => true];

	public $sourcePath = '@app/assets/actions_assets/user_view';

	public $css = [
	];

	public $js = [
		'js/main.js',
	];

	public $depends = [
		\yii\web\YiiAsset::class,
	];
}
