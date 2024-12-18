<?php

namespace app\assets;

use Yii;
use yii\web\AssetBundle;

use app\models\helpers\AssetHelper;


class BaseAsset extends AssetBundle
{

	public $publishOptions = ['forceCopy' => true];

	public $css = [
	];

	public $js = [
	];

	public $depends = [
	];

	public function init()
	{
		$this->css = AssetHelper::addVersion($this->css);
		$this->js = AssetHelper::addVersion($this->js);
		parent::init();
	}
}
