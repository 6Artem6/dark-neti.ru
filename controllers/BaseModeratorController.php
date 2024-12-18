<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\{Controller, NotFoundHttpException};


class BaseModeratorController extends Controller
{

	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => (
							!Yii::$app->user->isGuest and
							Yii::$app->user->identity->isModerator()
						),
						'roles' => ['@'],
					],
				],
				'denyCallback' => function ($rule, $action) {
					throw new NotFoundHttpException;
				},
			],
		];
	}

}
