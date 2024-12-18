<?php

namespace tests\unit\models;

use \Yii;
use yii\helpers\Html;
use app\models\question\{Question, Answer, Comment};
use app\models\user\User;


class CommentAnswerTest extends \Codeception\Test\Unit
{
	protected $model;
	protected $question;
	protected $answer;

    protected function _before()
    {
    	$user = User::findOne(1);
    	Yii::$app->user->login($user);
    	$this->model = new Comment(['scenario' => Comment::SCENARIO_CREATE_ANSWER]);

    	$this->question = new Question(['scenario' => Question::SCENARIO_CREATE]);
    	$this->question->question_title = 'Как решать РГР';
		$this->question->type_id = 1;
		$this->question->faculty_id = 1;
		$this->question->discipline_name = 'Экономика';
		$this->question->teachers = ['Гордячкова О.В.'];
		$this->question->tags = ['Задача'];
		$this->question->end_datetime = '11.09.2022 18:00';
		$this->question->question_text = 'Как нужно решить эту РГР?';
		$this->question->save();

		$this->answer = new Answer(['scenario' => Answer::SCENARIO_CREATE]);
		$this->answer->answer_text = 'Как решать РГР';
		$this->answer->question_id = $this->question->id;
		$this->answer->save();
    }

	public function testCreate_1()
	{
		$this->model->comment_text = 'Как решать РГР';
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->true();
		verify($this->model->validate('comment_text'))->true();
		verify($this->model->validate('answer_id'))->true();
		verify($this->model->save())->true();
	}

	public function testCreate_2()
	{
		$this->model->comment_text = null;
		$this->model->answer_id = null;

		verify($this->model->validate())->false();
		verify($this->model->validate('comment_text'))->false();
		verify($this->model->validate('answer_id'))->false();
	}

	public function testCreate_3()
	{
		$this->model->comment_text = '';
		$this->model->answer_id = 0;

		verify($this->model->validate())->false();
		verify($this->model->validate('comment_text'))->false();
		verify($this->model->validate('answer_id'))->false();
	}

	public function testCreate_4()
	{
		$this->model->comment_text = 1;
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->false();
		verify($this->model->validate('comment_text'))->false();
		verify($this->model->validate('answer_id'))->true();
	}

	public function testCreate_5()
	{
		$this->model->comment_text = str_repeat('1', 8192);
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->true();
		verify($this->model->validate('comment_text'))->true();
		verify($this->model->validate('answer_id'))->true();
	}

	public function testCreate_6()
	{
		$this->model->comment_text = str_repeat(' ', 8192);
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->false();
		verify($this->model->validate('comment_text'))->false();
		verify($this->model->validate('answer_id'))->true();
	}

	public function testCreate_7()
	{
		$this->model->comment_text = str_repeat("\n", 8192);
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->false();
		verify($this->model->validate('comment_text'))->false();
		verify($this->model->validate('answer_id'))->true();
	}

	public function testCreate_8()
	{
		$this->model->comment_text = str_repeat(" ", 8192 + 1);
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->false();
		verify($this->model->validate('comment_text'))->false();
		verify($this->model->validate('answer_id'))->true();
	}

	public function testCreate_9()
	{
		$this->model->comment_text = str_repeat(" ", 8192 + 1) . '1';
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->false();
		verify($this->model->validate('comment_text'))->false();
		verify($this->model->validate('answer_id'))->true();
	}

	public function testCreate_10()
	{
		$html = Html::tag('span', '');
		$this->model->comment_text = $html;
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->false();
		verify($this->model->validate('comment_text'))->false();
		verify($this->model->validate('answer_id'))->true();
	}

	public function testCreate_11()
	{
		$html = Html::tag('span', ' ');
		$this->model->comment_text = $html;
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->false();
		verify($this->model->validate('comment_text'))->false();
		verify($this->model->validate('answer_id'))->true();
	}

	public function testCreate_12()
	{
		$html = Html::tag('span', '1');
		$this->model->comment_text = $html;
		$this->model->answer_id = $this->answer->id;

		verify($this->model->validate())->true();
		verify($this->model->validate('comment_text'))->true();
		verify($this->model->validate('answer_id'))->true();
	}

}
