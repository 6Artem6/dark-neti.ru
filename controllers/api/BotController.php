<?php

namespace app\controllers\api;

use Yii;
use yii\filters\{AjaxFilter, VerbFilter};
use yii\helpers\{Url};
use yii\web\{Controller, NotFoundHttpException};

use app\models\service\Bot;


class BotController extends Controller
{

	public function behaviors()
	{
		return [];
	}

	public function beforeAction($action)
	{
		$this->enableCsrfValidation = false;
		if ($action->id !== 'index') {
			if ($secret = Yii::$app->request->get('secret_key')) {
				$bot = new Bot;
				if (!$bot->checkSecret($secret)) {
					throw new NotFoundHttpException;
				}
			}
		}
		return parent::beforeAction($action);
	}

	public function actionIndex()
	{
		throw new NotFoundHttpException;
	}

	public function actionSet()
	{
		$bot = new Bot;
		$result = $bot->set();
		echo $result;
	}

	public function actionUnset()
	{
		$bot = new Bot;
		$result = $bot->unset();
		echo $result;
	}

	public function actionHook()
	{
		$bot = new Bot;
		echo $bot->hook();
	}
}
