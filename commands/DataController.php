<?php

namespace app\commands;

use yii\console\{Controller, ExitCode};

use app\models\question\{Question, Answer, Comment};
use app\models\follow\FollowQuestion;
use app\models\like\{LikeAnswer, LikeComment};
use app\models\file\File;

use app\models\data\EduData;


class DataController extends Controller
{

	public function actionIndex()
	{
		return ExitCode::OK;
	}

	public function actionUpdate(bool $registerd_only = false, bool $schedule_only = false)
	{
		$model = new EduData;
		$model->updateGroupsSchedule($registerd_only, $schedule_only);

		return ExitCode::OK;
	}

	public function actionUpdateFio()
	{
		$model = new EduData;
		$model->getTeachersFio();

		return ExitCode::OK;
	}

	public function actionChairs()
	{
		$model = new EduData;
		$model->getChairs();

		return ExitCode::OK;
	}

	public function actionTeacherChair()
	{
		$model = new EduData;
		$model->getTeacherToChair();

		return ExitCode::OK;
	}

	public function actionDisciplineChair()
	{
		$model = new EduData;
		$model->getDisciplineToChair();

		return ExitCode::OK;
	}

	public function actionCreateQuestion()
	{
		$q = Question::find()->One();
		$q_new = new Question($q);
		$q_new->question_id = null;
		$q_new->discipline_name = $q->discipline->name;
		$q_new->save();

		for ($i = 0; $i < 128; ++$i) {
			$a = Answer::find()->one();
			$a_new = new Answer($a);
			$a_new->answer_id = null;
			$a_new->question_id = $q_new->question_id;
			$a_new->save();
			echo 'answer = '. $a_new->id . "\n";
			for ($j = 0; $j < 16; ++$j) {
				$c_a = Comment::find()->one();
				$c_a_new = new Comment($c_a);
				$c_a_new->scenario = Comment::SCENARIO_CREATE_ANSWER;
				$c_a_new->comment_id = null;
				$c_a_new->question_id = $q_new->question_id;
				$c_a_new->answer_id = $a_new->answer_id;
				$c_a_new->save();
				echo 'answer comment = '. $c_a_new->id . "\n";
			}
		}

		for ($k = 0; $k < 256; ++$k) {
			$c_q = Comment::find()->one();
			$c_q_new = new Comment($c_q);
			$c_q_new->scenario = Comment::SCENARIO_CREATE_QUESTION;
			$c_q_new->comment_id = null;
			$c_q_new->question_id = $q_new->question_id;
			$c_q_new->answer_id = null;
			$c_q_new->save();
			echo 'qusetion comment = '. $c_q_new->id . "\n";
		}
		echo 'question = ' . $q_new->id . "\n";
		return ExitCode::OK;
	}

	public function actionCreateQuestions()
	{
		$user_id = 15;
		for ($qi = 0; $qi < 4000; ++$qi) {
			$q = Question::find()->One();
			$q_new = new Question($q);
			$q_new->question_id = null;
			$q_new->discipline_name = $q->discipline->name;
			$q_new->save();

			for ($i = 0; $i < 4; ++$i) {
				$f = File::find()->where(['IS', 'answer_id', NULL])->one();
				$f_new = new File($f);
				$f_new->scenario = File::SCENARIO_CREATE_QUESTION;
				$f_new->file_id = null;
				$f_new->question_id = $q_new->id;
				$f_new->save();
				echo 'file = ' . $f_new->file_id . "\n";
			}

			$result = FollowQuestion::follow($q_new->id, $user_id);
			echo $result['message'] . "\n";

			for ($i = 0; $i < 8; ++$i) {
				$a = Answer::find()->one();
				$a_new = new Answer($a);
				$a_new->answer_id = null;
				$a_new->question_id = $q_new->id;
				$a_new->save();
				echo 'answer = ' . $a_new->id . "\n";

				$result = LikeAnswer::addLike($a_new->id, $user_id);
				echo $result['message'] . "\n";

				for ($j = 0; $j < 4; ++$j) {
					$c_a = Comment::find()->one();
					$c_a_new = new Comment($c_a);
					$c_a_new->scenario = Comment::SCENARIO_CREATE_ANSWER;
					$c_a_new->comment_id = null;
					$c_a_new->question_id = $q_new->id;
					$c_a_new->answer_id = $a_new->answer_id;
					$c_a_new->save();
					echo 'answer comment = ' . $c_a_new->id . "\n";

					$result = LikeComment::addLike($c_a_new->id, $user_id);
					echo $result['message'] . "\n";
				}
			}
			for ($k = 0; $k < 8; ++$k) {
				$c_q = Comment::find()->one();
				$c_q_new = new Comment($c_q);
				$c_q_new->scenario = Comment::SCENARIO_CREATE_QUESTION;
				$c_q_new->comment_id = null;
				$c_q_new->question_id = $q_new->id;
				$c_q_new->answer_id = null;
				$c_q_new->save();
				echo 'qusetion comment = ' . $c_q_new->id . "\n";

				$result = LikeComment::addLike($c_q_new->id, $user_id);
				echo $result['message'] . "\n";
			}
			echo 'question = ' . $q_new->id . "\n";
		}
		return ExitCode::OK;
	}

}
