<?php

namespace app\components\tuieditor;

use yii\web\AssetBundle;


class TuiEditorAsset extends AssetBundle
{

	public $sourcePath = '@app/components/tuieditor/assets';

	public $css = [
		'toastui-editor.min.css'
	];

	public $js = [
		'toastui-editor-all.min.js',
		'i18n/ru-ru.js'
	];

	public $depends = [
		'yii\web\YiiAsset',
	];
}
