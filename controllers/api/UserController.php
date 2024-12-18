<?php

namespace app\controllers\api;

use Yii;
use yii\filters\{AjaxFilter, VerbFilter};

use app\controllers\BaseController;

use app\models\question\{Question, Answer, Comment};
use app\models\request\Report;
use app\models\notification\Notification;
use app\models\follow\{FollowQuestion, FollowDiscipline, FollowUser};
use app\models\like\{LikeAnswer, LikeComment};

use app\widgets\notification\Message;


class UserController extends BaseController
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
			'message' => Yii::t('app', 'Welcome to DARK NETi API!')
		]);
	}

	public function actionFollowQuestion($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = FollowQuestion::follow($id, $user_id);
		return $this->asJson($result);
	}

	public function actionUnfollowQuestion($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = FollowQuestion::unfollow($id, $user_id);
		return $this->asJson($result);
	}

	public function actionFollowDiscipline($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = FollowDiscipline::follow($id, $user_id);
		return $this->asJson($result);
	}

	public function actionUnfollowDiscipline($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = FollowDiscipline::unfollow($id, $user_id);
		return $this->asJson($result);
	}

	public function actionFollowUser($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = FollowUser::follow($id, $user_id);
		return $this->asJson($result);
	}

	public function actionUnfollowUser($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = FollowUser::unfollow($id, $user_id);
		return $this->asJson($result);
	}

	public function actionAddLikeAnswer($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = LikeAnswer::addLike($id, $user_id);
		return $this->asJson($result);
	}

	public function actionRemoveLikeAnswer($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = LikeAnswer::removelike($id, $user_id);
		return $this->asJson($result);
	}

	public function actionAddLikeComment($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = LikeComment::addLike($id, $user_id);
		return $this->asJson($result);
	}

	public function actionRemoveLikeComment($id = 0)
	{
		$id = (int)$id;
		$user_id = Yii::$app->user->identity->id;
		$result = LikeComment::removelike($id, $user_id);
		return $this->asJson($result);
	}

	public function actionClose($id = 0)
	{
		$id = (int)$id;
		$result = Question::setClosed($id);
		return $this->asJson($result);
	}

	public function actionOpen($id = 0)
	{
		$id = (int)$id;
		$result = Question::setOpened($id);
		return $this->asJson($result);
	}

	public function actionQuestionDelete($id = 0)
	{
		$id = (int)$id;
		$result = Question::setDeleted($id);
		return $this->asJson($result);
	}

	public function actionQuestionRestore($id = 0)
	{
		$id = (int)$id;
		$result = Question::setRestored($id);
		return $this->asJson($result);
	}

	public function actionAnswerDelete($id = 0)
	{
		$id = (int)$id;
		$result = Answer::setDeleted($id);
		return $this->asJson($result);
	}

	public function actionAnswerRestore($id = 0)
	{
		$id = (int)$id;
		$result = Answer::setRestored($id);
		return $this->asJson($result);
	}

	public function actionCommentDelete($id = 0)
	{
		$id = (int)$id;
		$result = Comment::setDeleted($id);
		return $this->asJson($result);
	}

	public function actionCommentRestore($id = 0)
	{
		$id = (int)$id;
		$result = Comment::setRestored($id);
		return $this->asJson($result);
	}

	public function actionAnswerHelped($id = 0)
	{
		$id = (int)$id;
		$result = Answer::setHelped($id);
		return $this->asJson($result);
	}

	public function actionAnswerNotHelped($id = 0)
	{
		$id = (int)$id;
		$result = Answer::setNotHelped($id);
		return $this->asJson($result);
	}

	public function actionSwitchTheme()
	{
		Yii::$app->user->identity->data->switchTheme();
		return $this->asJson(true);
	}

	public function actionRemoveNotification($id = 0)
	{
		$id = (int)$id;
		$result = Notification::removeNotification($id);
		return $this->asJson($result);
	}

	public function actionRemoveAllNotifications()
	{
		$result = Notification::removeAllNotifications();
		return $this->asJson($result);
	}

	public function actionSeeLastNotifications($id = 0)
	{
		$id = (int)$id;
		$result = Notification::seeLastNotifications($id);
		return $this->asJson($result);
	}

	public function actionReportReject($id = 0)
	{
		$id = (int)$id;
		Report::setRejectedInType($id);
		return $this->asJson(true);
	}

	public function actionUpdateNotification()
	{
		Yii::$app->user->identity->data->checkLastOnline();
		$items = [];
		$notification = new Notification;
		$totalCount = $notification->totalCount;
		if ($totalCount) {
			foreach ($notification->getLastMessages() as $record) {
				$items[] = Message::widget([
					'model' => $record,
					'is_last_list' => true,
				]);
			}
		}
		return $this->asJson([
			'items' => $items,
			'totalCount' => $totalCount,
		]);
	}
}
