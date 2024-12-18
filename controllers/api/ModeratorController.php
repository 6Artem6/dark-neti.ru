<?php

namespace app\controllers\api;

use Yii;
use yii\filters\{AjaxFilter, VerbFilter};

use app\controllers\BaseModeratorController;

use app\models\question\{Question, Answer, Comment};
use app\models\data\RecordList;
use app\models\helpers\TextHelper;


class ModeratorController extends BaseModeratorController
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
			'message' => Yii::t('app', 'Welcome to DARK-NETi Moderator API!')
		]);
	}

	public function actionHideQuestion($id = 0)
	{
		$id = (int)$id;
		$result = Question::setHidden($id);
		return $this->asJson($result);
	}

	public function actionShowQuestion($id = 0)
	{
		$id = (int)$id;
		$result = Question::setShown($id);
		return $this->asJson($result);
	}

	public function actionHideAnswer($id = 0)
	{
		$id = (int)$id;
		$result = Answer::setHidden($id);
		return $this->asJson($result);
	}

	public function actionShowAnswer($id = 0)
	{
		$id = (int)$id;
		$result = Answer::setShown($id);
		return $this->asJson($result);
	}

	public function actionHideComment($id = 0)
	{
		$id = (int)$id;
		$result = Comment::setHidden($id);
		return $this->asJson($result);
	}

	public function actionShowComment($id = 0)
	{
		$id = (int)$id;
		$result = Comment::setShown($id);
		return $this->asJson($result);
	}

	public function actionTag()
	{
		$q = (string)Yii::$app->request->post('q');
		$discipline_id = (int)Yii::$app->request->post('discipline_id');
		$id = TextHelper::getIdFromUrl(Yii::$app->request->referrer, ['moderator', 'tag-edit']);
		$results = RecordList::getTagListByDiscipline($discipline_id, $q, $id);
		return $this->asJson(['results' => $results]);
	}
}
