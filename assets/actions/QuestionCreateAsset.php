<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class QuestionCreateAsset extends BaseAsset
{

	public $publishOptions = ['forceCopy' => true];

	public $sourcePath = '@app/assets/actions_assets/question_create';

	public $css = [
	];

	public $js = [
		'js/tour.js',
	];

	public $depends = [
		\yii\web\YiiAsset::class,
	];
}
