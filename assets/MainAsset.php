<?php

namespace app\assets;

use Yii;
use app\assets\BaseAsset;

use app\models\helpers\UserHelper;


class MainAsset extends BaseAsset
{

	public $publishOptions = ['forceCopy' => true];

	public $sourcePath = '@app/assets/main_assets';

	public $css = [
	];

	public $js = [
		'js/main.js',
	];

	public $depends = [
		\yii\web\YiiAsset::class,
		\app\assets\theme\BootstrapAsset::class,
		\app\assets\theme\BootstrapIconsAsset::class,
		\app\assets\theme\FontAwesomeAsset::class,
		\app\assets\theme\SpotlightAsset::class,
	];

	public function init()
	{
		if (UserHelper::isDarkTheme()) {
			$css = [
				['css/style.css', 'id' => 'style-light', 'rel' => 'stylesheet'],
				['css/style-dark.css', 'id' => 'style-dark', 'rel' => 'stylesheet'],
			];
		} else {
			$css = [
				['css/style-dark.css', 'id' => 'style-dark', 'rel' => 'stylesheet'],
				['css/style.css', 'id' => 'style-light', 'rel' => 'stylesheet'],
			];
		}
		$css[] = ['css/switch.css'];
		$this->css = array_merge($css, $this->css);
		parent::init();
	}
}
