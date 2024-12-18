<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class QuestionIndexAsset extends BaseAsset
{

	public $publishOptions = ['forceCopy' => true];

	public $sourcePath = '@app/assets/actions_assets/question_index';

	public $css = [
	];

	public $js = [
		'js/main.js',
	];

	public $depends = [
		\app\assets\UserAsset::class,
	];
}
