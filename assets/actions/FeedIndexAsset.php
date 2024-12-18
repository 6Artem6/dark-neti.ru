<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class FeedIndexAsset extends BaseAsset
{

	public $publishOptions = ['forceCopy' => true];

	public $sourcePath = '@app/assets/actions_assets/feed_index';

	public $css = [
	];

	public $js = [
		'js/main.js',
		'js/tour.js',
	];

	public $depends = [
		\yii\web\YiiAsset::class,
	];
}
