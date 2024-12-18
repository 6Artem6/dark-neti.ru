<?php

namespace app\controllers;

use Yii;

use app\models\question\{Question, Answer, Comment};


class HistoryController extends BaseController
{

	public function actionIndex()
	{
		return $this->redirect(['/question']);
	}

	public function actionQuestion(int $id = 0)
	{
		$question = Question::findOne($id);
		if (empty($question)) {
			Yii::$app->session->setFlash('info', Yii::t('app', 'Вопрос не был найден!'));
			return $this->redirect(['/question']);
		}
		if ($message = $question->canSee()) {
			Yii::$app->session->setFlash('info', $message);
			return $this->redirect($question->getRecordLink());
		}
		return $this->render('question', [
			'question' => $question,
		]);
	}

	public function actionAnswer(int $id = 0)
	{
		$answer = Answer::findOne($id);
		if (empty($answer)) {
			Yii::$app->session->setFlash('info', Yii::t('app', 'Ответ не был найден!'));
			return $this->redirect(['/question']);
		}
		if ($message = $answer->canSee()) {
			Yii::$app->session->setFlash('info', $message);
			return $this->redirect($answer->getRecordLink());
		}
		return $this->render('answer', [
			'answer' => $answer,
		]);
	}

	public function actionComment(int $id = 0)
	{
		$comment = Comment::findOne($id);
		if (empty($comment)) {
			Yii::$app->session->setFlash('info', Yii::t('app', 'Комментарий не был найден!'));
			return $this->redirect(['/question']);
		}
		if ($message = $comment->canSee()) {
			Yii::$app->session->setFlash('info', $message);
			return $this->redirect($comment->getRecordLink());
		}
		return $this->render('comment', [
			'comment' => $comment,
		]);
	}

}
