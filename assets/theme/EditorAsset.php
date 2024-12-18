<?php
namespace app\assets\theme;

use Yii;
use yii\web\AssetBundle;

class EditorAsset extends AssetBundle
{

	public $publishOptions = [
		'forceCopy' => true,
	];

	public $baseUrl = 'https://uicdn.toast.com/editor/latest/';

	public $css = [
		'toastui-editor.min.css'
	];

	public $js = [
		'toastui-editor-all.min.js',
		'i18n/ru-ru.js'
	];

	public $depends = [
	];
}
