<?php

namespace app\assets;

use Yii;
use app\assets\BaseAsset;


class ModeratorAsset extends BaseAsset
{

	public $sourcePath = '@app/assets/moderator_assets';

	public $css = [
	];

	public $js = [
		'js/moderator.js',
	];

	public $depends = [
		\app\assets\UserAsset::class,
	];
}
