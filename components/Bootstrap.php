<?php

namespace app\components;

use yii\base\BootstrapInterface;
use yii\web\GroupUrlRule;


class Bootstrap implements BootstrapInterface
{

	public function bootstrap($app)
	{
		$app->urlManager->addRules([
			[
				'pattern' => 'feed/index/<discipline:[\s\(\)\-\w-]+>',
				'route' => 'feed/index',
				'defaults' => ['discipline' => ''],
			]
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'question',
				'rules' => [
					[
						'pattern' => 'answer/<id:\d+>/<hash:[\w-]+>',
						'route' => 'answer',
						'defaults' => ['hash' => ''],
					],
					'edit/<id:\d+>' => 'edit',
					'followers/<id:\d+>' => 'followers',
					'document/<id:\d+>' => 'document',
					'document/<id:\d+>/<action:[\w-]+>' => 'document',
				],
			]
		]);

		$app->urlManager->addRules([
			'history/<action:[\w-]+>/<id:\d+>' => 'history/<action>'
		]);

		$app->urlManager->addRules([
			'file/<action:[\w-]+>/<id:\d+>' => 'file/<action>'
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'discipline',
				'rules' => [
					[
						'pattern' => 'view/<discipline:[\s\(\)\-\,\.\:\w-]+>/<tab:[\w-]+>',
						'route' => 'view',
						'defaults' => ['tab' => ''],
					],
				],
			]
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'user',
				'rules' => [
					[
						'pattern' => 'view/<username:[\w-]+>/<tab:[\w-]+>',
						'route' => 'view',
						'defaults' => ['tab' => ''],
					],
					'support-view/<id:\d+>' => 'support-view',
				],
			]
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'report',
				'rules' => [
					'more/<type:[\w-]+>' => 'more',
					'question/<id:\d+>' => 'question',
					'answer/<id:\d+>' => 'answer',
					'comment/<id:\d+>' => 'comment',
				],
			]
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'moderator',
				'rules' => [
					'limit-question/<id:\d+>' => 'limit-question',
					'limit-answer/<id:\d+>' => 'limit-answer',
					'limit-comment/<id:\d+>' => 'limit-comment',

					'tag-edit/<id:\d+>' => 'tag-edit',
				],
			]
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'api/user',
				'rules' => [
					[
						'pattern' => '<action:[\w-]+>/<id:\d+>',
						'route' => '<action>',
						'defaults' => ['id' => 0],
					],
				],
			]
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'api/save',
				'rules' => [
					'create-answer/<id:\d+>' => 'create-answer',
					'create-comment-question/<id:\d+>' => 'create-comment-question',
					'create-comment-answer/<id:\d+>' => 'create-comment-answer',

					'edit-question/<id:\d+>' => 'edit-question',
					'edit-answer/<id:\d+>' => 'edit-answer',
					'edit-comment/<id:\d+>' => 'edit-comment',

					'duplicate-question-request/<id:\d+>' => 'duplicate-question-request',
					'duplicate-question-response/<id:\d+>' => 'duplicate-question-response',
				],
			]
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'api/moderator',
				'rules' => [
					'<action:[\w-]+>/<id:\d+>' => '<action>',
				],
			]
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'api/save-moderator',
				'rules' => [
					'limit-question/<id:\d+>' => 'limit-question',
					'limit-answer/<id:\d+>' => 'limit-answer',
					'limit-comment/<id:\d+>' => 'limit-comment',
				],
			]
		]);

		$app->urlManager->addRules([
			[
				'class' => GroupUrlRule::class,
				'prefix' => 'api/info',
				'rules' => [
					'user/<id:\d+>' => 'user',
				],
			]
		]);
	}
}
