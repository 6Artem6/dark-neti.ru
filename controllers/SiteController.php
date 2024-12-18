<?php

namespace app\controllers;

use Yii;
use yii\filters\{AccessControl, VerbFilter};
use yii\bootstrap5\Html;

use app\models\LoginForm;
use app\models\register\Register;
use app\models\request\Support;


class SiteController extends BaseController
{

	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
				'layout' => 'main_base'
			],
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
				'testLimit' => 1,
				// 'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
			],
		];
	}

	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'only' => ['logout', 'index', 'login', 'register', 'verify', 'support'],
				'rules' => [
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
					[
						'actions' => ['login', 'register', 'verify', 'support'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'actions' => ['index'],
						'allow' => false,
						'roles' => ['?'],
						'denyCallback' => function($rule, $action) {
							return $this->redirect(['welcome']);
						}
					],
					[
						'actions' => ['index', 'login', 'register', 'verify'],
						'allow' => false,
						'roles' => ['@'],
						'denyCallback' => function($rule, $action) {
							return $this->redirect(['/feed']);
						}
					],
					[
						'actions' => ['support'],
						'allow' => false,
						'roles' => ['@'],
						'denyCallback' => function($rule, $action) {
							return $this->redirect(['/user/support']);
						}
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'logout' => ['post'],
				],
			],
		];
	}

	public function actionIndex()
	{
	}

	public function actionWelcome()
	{
		$this->layout = 'main_landing';

		$full_link = Yii::$app->params['telegram']['user']['FULL_LINK'];
		return $this->render('welcome', [
			'full_link' => $full_link,
		]);
	}

	public function actionLogin()
	{
		$this->layout = 'main_base';

		$model = new LoginForm;
		if ($model->load(Yii::$app->request->post()) && $model->login()) {
			if (!Yii::$app->user->identity->data->passed_tour) {
				return $this->redirect(['/feed', 'tour' => 1]);
			}
			return $this->redirect(['/feed']);
		}

		$model->password = '';
		return $this->render('login', [
			'model' => $model,
		]);
	}

	public function actionRegister()
	{
		$this->layout = 'main_base';
		$session = Yii::$app->session;

		$tour = false;
		if (!$session->get('tour')) {
			$tour = true;
			$session->set('tour', true);
		}
		$model = new Register(['scenario' => Register::SCENARIO_CREATE]);
		if ($model->load(Yii::$app->request->post())) {
			$url = Yii::$app->params['mailUrl'];
			$exists = $model->findExists();
			if (!empty($exists)) {
				if ($exists->isVerified()) {
					$session->setFlash('info',
						Yii::t('app', 'Ой. Твой аккаунт уже был создан.') . '<br>' .
						Yii::t('app', 'Если ты забыл пароль или логин, то обратись в поддержку.') .
						Html::tag('div', Html::img('/assistant/assistant_7.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
					);
					return $this->refresh();
				}
				if (!$exists->hasAttempts()) {
					$session->setFlash('info',
						Yii::t('app', 'Количество попыток на регистрацию уже исчерпано.') . '<br>' .
						Yii::t('app', 'Обратись в поддержку, если считаешь, что проблема возникла на нашей стороне.') .
						Html::tag('div', Html::img('/assistant/assistant_22.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
					);
					return $this->refresh();
				} else {
					if ($exists->hasRepeatMail()) {
						$session->setFlash('info',
							Yii::t('app', 'На твою студенческую почту повторно придёт письмо для подтверждения в течение 10-12 минут после прошлой попытки.') . '<br>' .
							Yii::t('app', 'Перейти в почтовый ящик: {link}.', ['link' => Html::a($url, $url, ['target' => '_blank'])]) .
							Html::tag('div', Html::img('/assistant/assistant_3.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
						);
						return $this->refresh();
					} elseif ($exists->newAttempt()) {
						$session->setFlash('success',
							Yii::t('app', 'На твою студенческую почту повторно будет отправлено письмо для подтверждения в течение 10-12 минут.') . '<br>' .
							Yii::t('app', 'Перейти в почтовый ящик: {link}.', ['link' => Html::a($url, $url, ['target' => '_blank'])]) .
							Html::tag('div', Html::img('/assistant/assistant_3.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
						);
						return $this->refresh();
					}
				}
			} else {
				if ($model->registerUser()) {
					$session->setFlash('success',
						Yii::t('app', 'Отлично! Твои данные успешно сохранены!') . '<br>' .
						Yii::t('app', 'Проверь свою студенческую почту, там тебя должно ждать письмо для подтверждения.') . '<br>' .
						Yii::t('app', 'Перейди в почтовый ящик по ссылке {link}, подтверди свою почту, потом туда придёт пароль и логин от твоего аккаунта.', [
							'link' => Html::a($url, $url, ['target' => '_blank'])
						]) . '<br>' .
						Yii::t('app', 'Вот и всё. Буду ждать тебя на сайте!') .
						Html::tag('div', Html::img('/assistant/assistant_21.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
					);
					return $this->refresh();
				}
			}
			if ($model->hasErrors()) {
				$session->setFlash('info',
					Yii::t('app', 'При сохранении данных были обнаружены ошибки.') . '<br>' .
					Yii::t('app', 'Пожалуйста, исправь их и попробуй снова.') .
					Html::tag('div', Html::img('/assistant/assistant_2.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
				);
			} elseif ($model->isBlocked()) {
				$session->setFlash('info',
					Yii::t('app', 'К сожалению, ты не можешь быть зарегистрирован на нашем Сайте.') . '<br>' .
					Yii::t('app', 'Если считаешь иначе - обратись, пожалуйста, в поддержку. Там тебе помогут.') .
					Html::tag('div', Html::img('/assistant/assistant_23.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
				);
				return $this->refresh();
			} elseif ($model->isWrongData()) {
				$session->setFlash('info',
					Yii::t('app', 'К сожалению, при заполнении данных была обнаружена проблема: {reason}.', [
						'reason' => $model->rejectReason->reason_text
					]) .
					Html::tag('div', Html::img('/assistant/assistant_24.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
				);
			}
		}

		return $this->render('register', [
			'model' => $model,
			'tour' => $tour
		]);
	}

	public function actionVerify()
	{
		$id = (int)Yii::$app->request->get('id');
		$code = (string)Yii::$app->request->get('code');
		$session = Yii::$app->session;

		$model = Register::findOne(['id' => $id, 'verify_code' => $code]);
		if (empty($model)) {
			$session->setFlash('info', Yii::t('app', 'Запись не найдена.'));
			return $this->goHome();
		}
		if (!$model->isCreated()) {
			$session->setFlash('info', Yii::t('app', 'Неверный статус записи.'));
			return $this->goHome();
		}
		if (!$model->createUser()) {
			$session->setFlash('info', Yii::t('app', 'Не удалось создать учётную запись. Похоже, она уже существует.'));
			return $this->goHome();
		}

		$url = Yii::$app->params['mailUrl'];
		$session->setFlash('success',
			Yii::t('app', 'Твоя запись успешно подтверждена!') . '<br>' .
			Yii::t('app', 'На твою студенческую почту отправлено письмо с учётными данными.') . '<br>' .
			Yii::t('app', 'Перейти в почтовый ящик: {link}.', ['link' => Html::a($url, $url, ['target' => '_blank'])]) .
			Html::tag('div', Html::img('/assistant/assistant_14.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
		);
		return $this->redirect('login');
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();

		return $this->goHome();
	}

	public function actionSupport()
	{
		$this->layout = 'main_base';
		$model = new Support(['scenario' => Support::SCENARIO_CREATE_GUEST]);
		$id = (int)Yii::$app->request->get('id');
		$model->setType($id);
		if ($model->load(Yii::$app->request->post())) {
			$session = Yii::$app->session;
			if ($model->save()) {
				$session->setFlash('success',
					Yii::t('app', 'Твоё обращение успешно отправлено!') . '<br>' .
					Yii::t('app', 'Мои разработчики ответят тебе в ближайшее время.') .
					Html::tag('div', Html::img('/assistant/assistant_9.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
				);
			} else {
				$session->setFlash('error',
					Yii::t('app', 'Твоё обращение не удалось отправить!') . '<br>' .
					Yii::t('app', 'Попробуй повторить через некоторое время.') .
					Html::tag('div', Html::img('/assistant/assistant_9.svg', ['width' => 250]), ['class' => 'w-100 text-center pt-3'])
				);
			}
			return $this->refresh();
		}
		return $this->render('support', [
			'model' => $model,
		]);
	}

}
