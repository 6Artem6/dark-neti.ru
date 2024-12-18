<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class SiteRegisterAsset extends BaseAsset
{

	public $sourcePath = '@app/assets/actions_assets/site_register';

	public $css = [
	];

	public $js = [
		'js/main.js'
	];

	public $depends = [
		\yii\web\YiiAsset::class,
		\yii\widgets\ActiveFormAsset::class
	];
}
