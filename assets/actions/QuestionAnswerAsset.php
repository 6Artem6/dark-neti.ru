<?php

namespace app\assets\actions;

use Yii;
use app\assets\BaseAsset;


class QuestionAnswerAsset extends BaseAsset
{

	public $sourcePath = '@app/assets/actions_assets/question_answer';

	public $css = [
	];

	public $js = [
		'js/main.js',
	];

	public $depends = [
		\app\assets\UserAsset::class,
	];
}
