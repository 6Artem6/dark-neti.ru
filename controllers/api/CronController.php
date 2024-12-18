<?php

namespace app\controllers\api;

use Yii;
use yii\filters\{AjaxFilter, VerbFilter};
use yii\helpers\{Url};
use yii\web\{Controller, NotFoundHttpException};

use app\models\register\CronRegisterMail;
use app\models\notification\CronNotification;
use app\models\service\{Cron, OcrAccount};


class CronController extends Controller
{

	public function behaviors()
	{
		return [
			'verbs'	=>	[
				'class' => VerbFilter::class,
				'actions' => [
					'*'  => ['GET'],
				],
			]
		];
	}

	public function beforeAction($action)
	{
		if ($action->id !== 'index') {
			if ($secret = Yii::$app->request->get('secret_key')) {
				$cron = new Cron;
				if (!$cron->checkSecret($secret)) {
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

	public function actionRegisterMail()
	{
		CronRegisterMail::checkRegisterMail();
	}

	public function actionNotification()
	{
		CronNotification::sendRecords();
	}

	public function actionOcrAttempts()
	{
		OcrAccount::checkRequestAttempts();
	}
}
