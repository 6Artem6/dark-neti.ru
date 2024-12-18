<?php

namespace app\assets;

use Yii;
use app\assets\BaseAsset;


class LandingAsset extends BaseAsset
{

	public $publishOptions = ['forceCopy' => true];

	public $sourcePath = '@app/assets/landing_assets';

	public $css = [
		'css/boxicons.min.css',
		'css/swiper-bundle.min.css',
		'css/theme.min.css',
		'css/style.css'
	];

	public $js = [
		'js/rellax.min.js',
		'js/smooth-scroll.polyfills.min.js',
		'js/swiper-bundle.min.js',
		'js/theme.min.js',
		'js/script.js',
	];

	public $depends = [
		\yii\web\JqueryAsset::class,
		\app\assets\theme\BootstrapAsset::class,
		\app\assets\theme\BootstrapIconsAsset::class,
	];
}
