<?php

namespace app\controllers;

use Yii;

use app\models\user\User;
use app\models\request\Support;
use app\models\search\UserSearch;
use app\models\notification\{
	Notification, NotificationSiteSettings, NotificationBotSettings
};


class UserController extends BaseController
{

	public function actionIndex()
	{
		$searchModel = new UserSearch;
		$provider = $searchModel->search(Yii::$app->request->get());
		return $this->render('index', [
			'searchModel' => $searchModel,
			'pages' => $searchModel->pages,
			'provider' => $provider,
		]);
	}

	public function actionView($username = '', $tab = 'about')
	{
		$username = (string)$username;
		$tab = (string)$tab;
		$model = User::findByUsername($username);
		if (empty($model)) {
			return $this->redirect(['view',
				'username' => Yii::$app->user->identity->username
			]);
		}
		return $this->render('view', [
			'model' => $model
		]);
	}

	public function actionNotification()
	{
		$notification = new Notification;
		$messages = $notification->getMessages();
		return $this->render('notification', [
			'messages' => $messages,
		]);
	}

	public function actionSettings()
	{
		$id = Yii::$app->user->identity->id;
		$site_model = NotificationSiteSettings::findOne($id);
		$bot_model = NotificationBotSettings::findOne($id);
		if (Yii::$app->request->isPost) {
			$post = Yii::$app->request->post();
			if (isset($post['default'])) {
				$site_model->returnToDefault();
				$bot_model->returnToDefault();
				Yii::$app->session->setFlash('success', Yii::t('app', 'Настройки выставлены по умолчанию!'));
				return $this->refresh();
			}
			if ($site_model->load($post) and $bot_model->load($post)) {
				if ($site_model->save() and $bot_model->save()) {
					Yii::$app->session->setFlash('success', Yii::t('app', 'Настройки сохранены!'));
					// return $this->redirect(['my']);
					return $this->refresh();
				} else {
					Yii::$app->session->setFlash('error', Yii::t('app', 'Настройки не сохранены!'));
					// return $this->refresh();
				}
			}
		}
		return $this->render('settings', [
			'site_model' => $site_model,
			'bot_model' => $bot_model,
		]);
	}

	public function actionTelegram()
	{
		$data = Yii::$app->user->identity->data;
		if (!is_null(Yii::$app->request->post('generate'))) {
			$data->createBotCode();
			return $this->refresh();
		}
		$bot_code = $data->bot_code;
		$has_chat = !empty($data->chat);
		$bot_data = Yii::$app->params['telegram']['bot'];
		return $this->render('telegram', [
			'bot_code' => $bot_code,
			'has_chat' => $has_chat,
			'short_link' => $bot_data['SHORT_LINK'],
			'full_link' => $bot_data['FULL_LINK'],
		]);
	}

	public function actionSupport()
	{
		$model = new Support(['scenario' => Support::SCENARIO_CREATE]);

		$id = Yii::$app->request->get('id');
		$text = Yii::$app->request->get('text');
		$model->setType($id);
		$model->setText($text);
		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				Yii::$app->session->setFlash('success', Yii::t('app', 'Обращение отправлено!'));
				return $this->redirect($this->action->id);
			} else {
				Yii::$app->session->setFlash('error', Yii::t('app', 'Обращение не отправлено!'));
			}
		}
		$model->setAuthorListSeen();
		$list = $model->getAuthorList();
		return $this->render('support', [
			'model' => $model,
			'list' => $list,
		]);
	}

	public function actionSupportView($id = 0)
	{
		$id = (int)$id;
		$model = Support::findOne($id);
		if (!$model->isAuthor) {
			Yii::$app->session->setFlash('info', Yii::t('app', 'Вы не можете просматривать данную запись!'));
			return $this->redirect(['support']);
		}
		return $this->render('support_view', [
			'model' => $model,
		]);
	}
}
