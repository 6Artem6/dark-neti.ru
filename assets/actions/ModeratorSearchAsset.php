<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class ModeratorSearchAsset extends BaseAsset
{

	public $sourcePath = '@app/assets/actions_assets/moderator_search';

	public $css = [
	];

	public $js = [
		'js/main.js',
	];

	public $depends = [
		\yii\web\YiiAsset::class,
	];
}
