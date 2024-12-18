<?php

namespace app\controllers\api;

use Yii;
use yii\filters\{AjaxFilter, VerbFilter};
use yii\bootstrap5\ActiveForm;

use app\controllers\BaseController;

use app\models\question\{Question, Answer, Comment};
use app\models\request\{Report, DuplicateQuestionRequest};
use app\models\helpers\HtmlHelper;


class SaveController extends BaseController
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
			'message' => Yii::t('app', 'Welcome to DARK-NETi Save API!')
		]);
	}

	public function actionCreateQuestion()
	{
		$ok = false;
		$message = Yii::t('app', 'Не удалось получить данные.');
		$link = null;
		$model = new Question(['scenario' => Question::SCENARIO_CREATE]);
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Вопрос создан!');
				Yii::$app->session->setFlash('success', $message);
				$link = $model->getRecordLink();
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
			'link' => $link,
		]);
	}

	public function actionCreateAnswer($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Не удалось получить данные.');
		$link = null;

		$id = (int)$id;
		$model = new Answer(['scenario' => Answer::SCENARIO_CREATE]);
		$model->question_id = $id;
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Ответ создан!');
				Yii::$app->session->setFlash('success', $message);
				$link = $model->getRecordLink();
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
			'link' => $link,
		]);
	}

	public function actionCreateCommentQuestion($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Не удалось получить данные.');
		$link = null;

		$id = (int)$id;
		$model = new Comment(['scenario' => Comment::SCENARIO_CREATE_QUESTION]);
		$model->question_id = $id;
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Комментарий создан!');
				Yii::$app->session->setFlash('success', $message);
				$link = $model->getRecordLink();
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
			'link' => $link,
		]);
	}

	public function actionCreateCommentAnswer($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Не удалось получить данные.');
		$link = null;

		$id = (int)$id;
		$model = new Comment(['scenario' => Comment::SCENARIO_CREATE_ANSWER]);
		$model->answer_id = $id;
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Комментарий создан!');
				Yii::$app->session->setFlash('success', $message);
				$link = $model->getRecordLink();
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
			'link' => $link,
		]);
	}

	public function actionEditQuestion($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Вопрос не был найден!');
		$link = null;

		$id = (int)$id;
		$model = Question::findOne($id);
		if (empty($model)) {
			return $this->asJson([
				'status' => $ok,
				'message' => $message,
			]);
		}
		if ($message = $model->canEdit()) {
			return $this->asJson([
				'status' => $ok,
				'message' => $message,
			]);
		}

		$model->setEditing();
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Вопрос изменён!');
				Yii::$app->session->setFlash('success', $message);
				$link = $model->getRecordLink();
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
			'link' => $link,
		]);
	}

	public function actionEditAnswer($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Ответ не был найден!');
		$link = null;

		$id = (int)$id;
		$model = Answer::findOne($id);
		if (empty($model)) {
			return $this->asJson([
				'status' => $ok,
				'message' => $message,
			]);
		}
		if ($message = $model->canEdit()) {
			return $this->asJson([
				'status' => $ok,
				'message' => $message,
			]);
		}

		$model->setEditing();
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Ответ изменён!');
				Yii::$app->session->setFlash('success', $message);
				$link = $model->getRecordLink();
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
			'link' => $link,
		]);
	}

	public function actionEditComment($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Комментарий не был найден!');
		$link = null;

		$id = (int)$id;
		$model = Comment::findOne($id);
		if (empty($model)) {
			return $this->asJson([
				'status' => $ok,
				'message' => $message,
			]);
		}
		if ($message = $model->canEdit()) {
			return $this->asJson([
				'status' => $ok,
				'message' => $message,
			]);
		}

		$model->setEditing();
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Ответ изменён!');
				Yii::$app->session->setFlash('success', $message);
				$link = $model->getRecordLink();
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
			'link' => $link,
		]);
	}

	public function actionReportQuestion($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Не удалось получить данные.');

		$id = (int)$id;
		$model = new Report(['scenario' => Report::SCENARIO_QUESTION]);
		$model->question_id = $id;
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Обращение отправлено.');
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
		]);
	}

	public function actionReportAnswer($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Не удалось получить данные.');

		$id = (int)$id;
		$model = new Report(['scenario' => Report::SCENARIO_ANSWER]);
		$model->answer_id = $id;
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Обращение отправлено.');
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
		]);
	}

	public function actionReportComment($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Не удалось получить данные.');

		$id = (int)$id;
		$model = new Report(['scenario' => Report::SCENARIO_COMMENT]);
		$model->comment_id = $id;
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Обращение отправлено.');
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
		]);
	}

	public function actionDuplicateQuestionRequest($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Не удалось получить данные.');

		$id = (int)$id;
		$model = new DuplicateQuestionRequest(['scenario' => DuplicateQuestionRequest::SCENARIO_REQUEST]);
		$model->question_id = $id;
		if ($model->load(Yii::$app->request->post())) {
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				$message = Yii::t('app', 'Вопрос предложен');
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
		]);
	}

	public function actionDuplicateQuestionResponse($id = 0)
	{
		$ok = false;
		$message = Yii::t('app', 'Не удалось получить данные.');

		$id = (int)$id;
		$model = DuplicateQuestionRequest::findOne($id);
		if (empty($model)) {
			return $this->asJson([
				'status' => $ok,
				'message' => $message,
			]);
		}

		$model->setResponsing();
		if ($model->load(Yii::$app->request->post())) {
			if (Yii::$app->request->post('button') == 'question-response-accept') {
				$model->request_status = DuplicateQuestionRequest::STATUS_ACCEPTED;
			} elseif (Yii::$app->request->post('button') == 'question-response-reject') {
				$model->request_status = DuplicateQuestionRequest::STATUS_REJECTED;
			}
			if (!is_null(Yii::$app->request->post('validate'))) {
				return $this->asJson(ActiveForm::validate($model));
			}
			if ($model->save()) {
				$ok = true;
				if ($model->request_status == DuplicateQuestionRequest::STATUS_ACCEPTED) {
					$message = Yii::t('app', 'Вопрос принят');
				} else {
					$message = Yii::t('app', 'Вопрос отклонён');
				}
			} else {
				$message = HtmlHelper::errorSummary($model);
			}
		}
		return $this->asJson([
			'status' => $ok,
			'message' => $message,
		]);
	}
}
