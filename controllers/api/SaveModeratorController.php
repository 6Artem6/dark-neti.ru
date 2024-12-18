<?php

namespace app\controllers\api;

use Yii;
use yii\filters\{AjaxFilter, VerbFilter};
use yii\bootstrap5\ActiveForm;

use app\controllers\BaseModeratorController;

use app\models\question\{Question, Answer, Comment};
use app\models\user\UserLimit;
use app\models\helpers\HtmlHelper;


class SaveModeratorController extends BaseModeratorController
{

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = [
			'class' => VerbFilter::class,
			'actions' => [
				'*'  => ['POST'],
				'index'  => ['GET'],
			],
		];
		$behaviors[] = [
			'class' => AjaxFilter::class
		];
		return $behaviors;
	}


	public function actionIndex()
	{
		return $this->asJson([
			'status' => true,
			'message' => Yii::t('app', 'Welcome to DARK NETi Moderator Save API!')
		]);
	}

	public function actionLimitQuestion($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Вопрос не был найден!');

		$id = (int)$id;
		$record = Question::findOne($id);
		if (empty($record)) {
			return $this->asJson([
				'status' => $ok,
				'message' => $message,
			]);
		}

		$model = UserLimit::findOne($record->author->id);
		$model->scenario = UserLimit::SCENARIO_QUESTION;
		$model->question_id = $id;
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Ограничение изменено!');
				Yii::$app->session->setFlash('success', $message);
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message
		]);
	}
}
