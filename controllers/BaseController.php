<?php

namespace app\controllers;

use Yii;
use yii\filters\{AccessControl, RateLimiter};
use yii\web\Controller;


class BaseController extends Controller
{

	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
						'denyCallback' => function($rule, $action) {
							return $this->redirect(['/site/login']);
						}
					],
				],
			],
			'rateLimiter' => [
				'class' => RateLimiter::class,
				'user' => function() {
					return Yii::$app->user->identity;
				}
			]
		];
	}

}
