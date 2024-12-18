<?php

namespace app\models\helpers;

use Yii;
use yii\base\Model;
use yii\web\View;


class AssetHelper extends Model
{

	public static function addVersion(array $files)
	{
		$v = Yii::$app->params['assetVersion'];
		foreach ($files as $k => $file) {
			if (is_array($file)) {
				$files[$k][0] = $files[$k][0] . '?v=' . $v;
			} else {
				$files[$k] = $files[$k] . '?v=' . $v;
			}
		}
		return $files;
	}

	public static function getViewScripts(View $view)
	{
		$scripts = null;
		if (!empty($view->js)) {
			foreach ($view->js as $js) {
				$scripts .= implode("\n", $js) . "\n";
			}
		}
		return $scripts;
	}
}
