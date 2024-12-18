<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class DisciplineViewAsset extends BaseAsset
{

	public $publishOptions = ['forceCopy' => true];

	public $sourcePath = '@app/assets/actions_assets/discipline_view';

	public $css = [
	];

	public $js = [
		'js/main.js',
	];

	public $depends = [
		\yii\web\YiiAsset::class,
	];
}
