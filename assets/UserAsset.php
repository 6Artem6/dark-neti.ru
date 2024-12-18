<?php

namespace app\assets;

use Yii;
use yii\web\AssetBundle;


class UserAsset extends BaseAsset
{

	public $publishOptions = ['forceCopy' => true];

	public $sourcePath = '@app/assets/user_assets';

	public $css = [
	];

	public $js = [
		'js/user.js',
	];

	public $depends = [
		\app\assets\MainAsset::class,
		\app\assets\theme\SliderAsset::class,
		\app\assets\theme\ScrollbarAsset::class,
		\app\assets\theme\ImageViewerAsset::class,
	];
}
