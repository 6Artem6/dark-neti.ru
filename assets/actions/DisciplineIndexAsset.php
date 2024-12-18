<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class DisciplineIndexAsset extends BaseAsset
{

	public $sourcePath = '@app/assets/actions_assets/discipline_index';

	public $css = [
	];

	public $js = [
		'js/tour.js'
	];

	public $depends = [
		\yii\web\YiiAsset::class,
	];
}
