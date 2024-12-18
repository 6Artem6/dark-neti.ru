<?php

namespace app\controllers\api;

use Yii;
use yii\filters\{AjaxFilter, VerbFilter};
use yii\data\Pagination;

use app\controllers\BaseController;

use app\models\question\{Answer, Comment, Question, Tag};
use app\models\data\RecordList;
use app\models\request\ReportType;
use app\models\search\NavbarSearch;

use app\models\helpers\AssetHelper;

use app\widgets\question\{
	FeedRecord, AnswerBody, CommentBody
};
use app\widgets\form\{
	DuplicateQuestionRequestForm,
	DuplicateQuestionResponseForm
};


class ListController extends BaseController
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

	public $layout = false;


	public function actionIndex()
	{
		return $this->asJson([
			'status' => true,
			'message' => Yii::t('app', 'Welcome to DARK-NETi List API!')
		]);
	}

	public function actionSearch()
	{
		$q = (string)Yii::$app->request->post('q');
		$searchModel = new NavbarSearch;
		$results = $searchModel->getList($q);
		return $this->asJson($results);
	}

	public function actionTeacher()
	{
		$q = (string)Yii::$app->request->post('q');
		$discipline_name = (string)Yii::$app->request->post('discipline_name');
		$faculty_id = (int)Yii::$app->request->post('faculty_id');
		$results = RecordList::getTeacherListByDiscipline($discipline_name, $faculty_id, $q);
		return $this->asJson(['results' => $results]);
	}

	public function actionTag()
	{
		$q = (string)Yii::$app->request->post('q');
		$discipline_name = (string)Yii::$app->request->post('discipline_name');
		$results = RecordList::getTagQuestionListByDiscipline($discipline_name, q: $q);
		return $this->asJson([
			'results' => $results
		]);
	}

	public function actionDuplicateQuestionRequest()
	{
		$items = [];
		$error = null;
		$q = (string)Yii::$app->request->post('q');
		$question_id = (int)Yii::$app->request->post('id');
		$list = RecordList::getDuplicateQuestionList($question_id, $q);
		foreach ($list as $params) {
			$items[] = DuplicateQuestionRequestForm::widget([
				'model' => $params['model'],
				'is_reported' => $params['is_reported'],
				'question_id' => $question_id,
			]);
		}
		if (!$items) {
			$error = Yii::t('app', 'Вопросы не найдены.');
		}
		$scripts = AssetHelper::getViewScripts($this->view);
		return $this->asJson([
			'items' => $items,
			'scripts' => $scripts,
			'error' => $error,
		]);
	}

	public function actionDuplicateQuestionResponse()
	{
		$items = [];
		$error = null;
		$question_id = (int)Yii::$app->request->post('id');
		$list = RecordList::getDuplicateQuestionRequestList($question_id);
		foreach ($list as $record) {
			$items[] = DuplicateQuestionResponseForm::widget([
				'model' => $record
			]);
		}
		if (!$items) {
			$error = Yii::t('app', 'Вопросы не найдены.');
		}
		$scripts = AssetHelper::getViewScripts($this->view);
		return $this->asJson([
			'items' => $items,
			'scripts' => $scripts,
			'error' => $error,
		]);
	}

	public function actionFeed()
	{
		$items = [];
		$is_end = false;
		$is_empty = false;

		$page = (int)Yii::$app->request->get('page');
		$discipline = (string)Yii::$app->request->post('discipline');
		[$list, $pages] = RecordList::getQuestionFeedList($discipline);
		$page_size = 10;

		$report_types = ReportType::getListsByType();
		if ($pages->totalCount) {
			if ($pages->totalCount > $page_size * ($page - 1)) {
				foreach ($list as $record) {
					$items[] = FeedRecord::widget([
						'model' => $record,
						'report_types' => $report_types,
					]);
				}
			}
			if ($pages->totalCount <= $page_size * $page) {
				$is_end = true;
			}
		} else {
			$is_empty = true;
		}
		$scripts = AssetHelper::getViewScripts($this->view);
		return $this->asJson([
			'items' => $items,
			'totalCount' => $pages->totalCount,
			'is_end' => $is_end,
			'is_empty' => $is_empty,
			'scripts' => $scripts,
		]);
	}

	public function actionQuestionAnswer()
	{
		$items = [];
		$is_end = false;
		$is_empty = false;

		$page = (int)Yii::$app->request->get('page');
		$question_id = (string)Yii::$app->request->post('id');
		$list = Answer::getQuestionAnswerList($question_id);
		$page_size = 25;
		$pages = new Pagination([
			'totalCount' => count($list),
			'defaultPageSize' => $page_size,
		]);

		$total = count($list);
		$list = array_slice($list, $pages->offset);
		$report_types = ReportType::getAnswerList();
		if ($pages->totalCount) {
			if ($pages->totalCount > $page_size * ($page - 1)) {
				foreach ($list as $record) {
					$items[] = AnswerBody::widget([
						'model' => $record,
						'report_types' => $report_types,
						'is_feed' => false,
						'is_unseen' => false,
					]);
				}
			}
			if ($pages->totalCount <= $page_size * $page) {
				$is_end = true;
			}
		} else {
			$is_empty = true;
		}
		$scripts = AssetHelper::getViewScripts($this->view);
		return $this->asJson([
			'items' => $items,
			'totalCount' => $total,
			'is_end' => $is_end,
			'is_empty' => $is_empty,
			'scripts' => $scripts,
		]);
	}

	public function actionQuestionAnswerHelped()
	{
		$items = [];
		$is_end = false;
		$is_empty = false;

		$page = (int)Yii::$app->request->get('page');
		$question_id = (string)Yii::$app->request->post('id');
		$list = Answer::getQuestionAnswerHelpedList($question_id);
		$page_size = 25;
		$pages = new Pagination([
			'totalCount' => count($list),
			'defaultPageSize' => $page_size,
		]);

		$total = count($list);
		$list = array_slice($list, $pages->offset);
		$report_types = ReportType::getAnswerList();
		if ($pages->totalCount) {
			if ($pages->totalCount > $page_size * ($page - 1)) {
				foreach ($list as $record) {
					$items[] = AnswerBody::widget([
						'model' => $record,
						'report_types' => $report_types,
						'is_feed' => false,
						'is_unseen' => false,
					]);
				}
			}
			if ($pages->totalCount <= $page_size * $page) {
				$is_end = true;
			}
		} else {
			$is_empty = true;
		}
		$scripts = AssetHelper::getViewScripts($this->view);
		return $this->asJson([
			'items' => $items,
			'totalCount' => $total,
			'is_end' => $is_end,
			'is_empty' => $is_empty,
			'scripts' => $scripts,
		]);
	}

	public function actionQuestionComments()
	{
		$items = [];
		$question_id = (int)Yii::$app->request->post('id');
		$question = Question::find()
			->from(['question' => Question::tableName()])
			->joinWith('questionComments')
			->where(['question.question_id' => $question_id])
			->one();
		$total = 0;
		if (!empty($question)) {
			if ($comments = $question->questionComments) {
				$total = count($comments);
				$report_types = ReportType::getCommentList();
				foreach ($comments as $comment) {
					$items[] = CommentBody::widget([
						'model' => $comment,
						'report_types' => $report_types,
						'is_feed' => false,
						'is_unseen' => false,
					]);
				}
			}
		}
		$scripts = AssetHelper::getViewScripts($this->view);
		return $this->asJson([
			'items' => $items,
			'totalCount' => $total,
			'scripts' => $scripts,
		]);
	}

	public function actionAnswerComments()
	{
		$items = [];
		$answer_id = (int)Yii::$app->request->post('id');
		$answer = Answer::find()
			->from(['answer' => Answer::tableName()])
			->joinWith('comments')
			->where(['answer.answer_id' => $answer_id])
			->one();
		$report_types = ReportType::getCommentList();
		if (!empty($answer) and ($comments = $answer->commentListHelpful)) {
			foreach ($comments as $comment) {
				$items[] = CommentBody::widget([
					'model' => $comment,
					'report_types' => $report_types,
					'is_feed' => false,
					'is_unseen' => false,
				]);
			}
		}
		$scripts = AssetHelper::getViewScripts($this->view);
		return $this->asJson([
			'items' => $items,
			'scripts' => $scripts,
		]);
	}
}
