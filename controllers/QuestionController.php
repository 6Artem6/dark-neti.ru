<?php

namespace app\controllers;

use Yii;
use yii\data\Pagination;

use app\models\question\Question;
use app\models\request\ReportType;
use app\models\search\QuestionSearch;
use app\models\notification\Notification;


class QuestionController extends BaseController
{

	public function actionIndex()
	{
		$searchModel = new QuestionSearch;
		$list = $searchModel->search(Yii::$app->request->get());
		$report_types = ReportType::getListsByType();
		return $this->render('index', [
			'searchModel' => $searchModel,
			'pages' => $searchModel->pages,
			'list' => $list,
			'report_types' => $report_types,
		]);
	}

	public function actionCreate()
	{
		$model = new Question(['scenario' => Question::SCENARIO_CREATE]);
		if ($message = $model->canCreate()) {
			Yii::$app->session->setFlash('info', $message);
			return $this->redirect(['index']);
		}
		if (Yii::$app->request->get('test') == 1) {
			$model->loadTest();
		}
		return $this->render('create', [
			'model' => $model,
		]);
	}

	public function actionEdit($id = 0)
	{
		$id = (int)$id;
		$model = Question::findOne($id);
		if (empty($model)) {
			Yii::$app->session->setFlash('info', Yii::t('app', 'Вопрос не был найден!'));
			return $this->redirect(['index']);
		}
		if ($message = $model->canEdit()) {
			Yii::$app->session->setFlash('info', $message);
			return $this->redirect($model->getRecordLink());
		}
		$model->setEditing();
		return $this->render('edit', [
			'model' => $model,
		]);
	}

	public function actionAnswer($id = 0, $hash = '')
	{
		$id = (int)$id;
		$question = Question::getRecordForAnswer($id);
		if (empty($question)) {
			Yii::$app->session->setFlash('info', Yii::t('app', 'Вопрос не был найден!'));
			return $this->redirect(['index']);
		}
		$unseen_list = Notification::getUnseenList($question);
		$new_list = $question->getNewByDisciplineList();
		$report_types = ReportType::getListsByType();
		$question->trigger(Question::EVENT_BEFORE_ANSWER);
		return $this->render('answer', [
			'question' => $question,
			'unseen_list' => $unseen_list,
			'new_list' => $new_list,
			'report_types' => $report_types,
		]);
	}

	public function actionFollowers($id = 0)
	{
		$id = (int)$id;
		$question = Question::getRecordForFollow($id);
		if (empty($question)) {
			Yii::$app->session->setFlash('info', Yii::t('app', 'Вопрос не был найден!'));
			return $this->redirect(['index']);
		}
		$report_types = ReportType::getListsByType();
		$question->trigger(Question::EVENT_BEFORE_ANSWER);

		$list = $question->follow;
		$pages = new Pagination([
			'totalCount' => count($list),
			'defaultPageSize' => 10,
		]);
		$list = array_slice($list, $pages->offset, $pages->limit);

		return $this->render('followers', [
			'question' => $question,
			'list' => $list,
			'pages' => $pages,
			'report_types' => $report_types,
		]);
	}

}
