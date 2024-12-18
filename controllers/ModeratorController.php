<?php

namespace app\controllers;

use Yii;

use app\models\question\{Question, Answer, Comment, Tag};
use app\models\user\{User, Register, UserLimit};
use app\models\request\{Report, Support};
use app\models\search\{ReportSearch, TagSearch};


class ModeratorController extends BaseModeratorController
{

	public function actionIndex()
	{
		return $this->redirect(['report']);
	}

	public function actionReport()
	{
		$support_list = Support::getToModeratorSentList();
		$report_list = Report::getNotClosedList();
		return $this->render('report', [
			'support_list' => $support_list,
			'report_list' => $report_list,
		]);
	}

	public function actionSupportSearch()
	{
		$type = ReportSearch::TYPE_SUPPORT;
		$searchModel = new ReportSearch;
		$provider = $searchModel->search(Yii::$app->request->get(), $type);
		return $this->render('support', [
			'searchModel' => $searchModel,
			'pages' => $searchModel->pages,
			'provider' => $provider,
		]);
	}

	public function actionSupportResponse(int $id = 0)
	{
		$model = Support::findOne($id);
		if (empty($model)) {
			return $this->redirect(['support']);
		}
		$model->scenario = Support::SCENARIO_RESPONSE;
		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				Yii::$app->session->setFlash('success', Yii::t('app', 'Ответ отправлен!'));
				return $this->refresh();
			} else {
				Yii::$app->session->setFlash('error', Yii::t('app', 'Ответ не отправлен!'));
			}
		}
		return $this->render('support_response', [
			'model' => $model
		]);
	}

	public function actionQuestionSearch()
	{
		$type = ReportSearch::TYPE_QUESTION;
		$searchModel = new ReportSearch;
		$provider = $searchModel->search(Yii::$app->request->get(), $type);
		return $this->render('comment', [
			'searchModel' => $searchModel,
			'pages' => $searchModel->pages,
			'provider' => $provider,
		]);
	}

	public function actionAnswerSearch()
	{
		$type = ReportSearch::TYPE_ANSWER;
		$searchModel = new ReportSearch;
		$provider = $searchModel->search(Yii::$app->request->get(), $type);
		return $this->render('comment', [
			'searchModel' => $searchModel,
			'pages' => $searchModel->pages,
			'provider' => $provider,
		]);
	}

	public function actionCommentSearch()
	{
		$type = ReportSearch::TYPE_COMMENT;
		$searchModel = new ReportSearch;
		$provider = $searchModel->search(Yii::$app->request->get(), $type);
		return $this->render('comment', [
			'searchModel' => $searchModel,
			'pages' => $searchModel->pages,
			'provider' => $provider,
		]);
	}

	public function actionLimitQuestion(int $id = 0)
	{
		$record = Question::findOne($id);
		if (!$record) {
			Yii::$app->session->setFlash('error', Yii::t('app', 'Запись не найдена.'));
			return $this->redirect(['questions']);
		}
		$model = UserLimit::findOne($record->author->id);
		return $this->render('limit_question', [
			'record' => $record,
			'model' => $model,
		]);
	}

	public function actionLimitAnswer(int $id = 0)
	{
		$record = Answer::findOne($id);
		if (!$record) {
			Yii::$app->session->setFlash('error', Yii::t('app', 'Запись не найдена.'));
			return $this->redirect(['answers']);
		}
		$model = UserLimit::findOne($record->author->id);
		return $this->render('limit_answer', [
			'record' => $record,
			'model' => $model,
		]);
	}

	public function actionLimitComment(int $id = 0)
	{
		$record = Comment::findOne($id);
		if (!$record) {
			Yii::$app->session->setFlash('error', Yii::t('app', 'Запись не найдена.'));
			return $this->redirect(['comments']);
		}
		$model = UserLimit::findOne($record->author->id);
		return $this->render('limit_comment', [
			'record' => $record,
			'model' => $model,
		]);
	}

	public function actionTags()
	{
		$searchModel = new TagSearch;
		$provider = $searchModel->search(Yii::$app->request->get());
		return $this->render('tags', [
			'searchModel' => $searchModel,
			'pages' => $searchModel->pages,
			'provider' => $provider,
		]);
	}

	public function actionTagCreate()
	{
		$model = new Tag;
		$model->scenario = Tag::SCENARIO_MODERATOR;
		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				Yii::$app->session->setFlash('info', Yii::t('app', 'Запись создана.'));
				return $this->redirect(['tag-edit', 'id' => $model->tag_id]);
			}
			Yii::$app->session->setFlash('error', Yii::t('app', 'Не удалось создать запись.'));
		}
		return $this->render('tag', [
			'model' => $model,
		]);
	}

	public function actionTagEdit(int $id = 0)
	{
		$model = Tag::findOne($id);
		if (!$model) {
			Yii::$app->session->setFlash('error', Yii::t('app', 'Запись не найдена.'));
			return $this->redirect(['tags']);
		}
		$model->setEditing();
		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				Yii::$app->session->setFlash('info', Yii::t('app', 'Запись изменена.'));
				return $this->refresh();
			}
			Yii::$app->session->setFlash('error', Yii::t('app', 'Не удалось изменить запись.'));
		}
		return $this->render('tag', [
			'model' => $model,
		]);
	}

	public function actionRegister()
	{
		// $list = Register::find()->all();
	}

	public function actionTest()
	{
	}

}
