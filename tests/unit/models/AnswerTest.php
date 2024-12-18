<?php

namespace tests\unit\models;

use \Yii;
use yii\helpers\Html;
use app\models\question\{Question, Answer};
use app\models\user\User;


class AnswerTest extends \Codeception\Test\Unit
{
	protected $model;
	protected $question;

    protected function _before()
    {
    	$user = User::findOne(1);
    	Yii::$app->user->login($user);
    	$this->model = new Answer(['scenario' => Answer::SCENARIO_CREATE]);

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
    }

	public function testCreate_1()
	{
		$this->model->answer_text = 'Как решать РГР';
		$this->model->question_id = $this->question->id;

		verify($this->model->validate())->true();
		verify($this->model->validate('answer_text'))->true();
		verify($this->model->validate('question_id'))->true();
		verify($this->model->save())->true();
	}

	public function testCreate_2()
	{
		$this->model->answer_text = null;
		$this->model->question_id = null;

		verify($this->model->validate('answer_text'))->false();
		verify($this->model->validate('question_id'))->false();
	}

	public function testCreate_3()
	{
		$this->model->answer_text = '';
		$this->model->question_id = 0;

		verify($this->model->validate('answer_text'))->false();
		verify($this->model->validate('question_id'))->false();
	}

	public function testCreate_4()
	{
		$this->model->answer_text = 1;
		$this->model->question_id = $this->question->id;

		verify($this->model->validate('answer_text'))->false();
		verify($this->model->validate('question_id'))->true();
	}

	public function testCreate_5()
	{
		$this->model->answer_text = str_repeat('1', 8192);
		$this->model->question_id = $this->question->id;

		verify($this->model->validate('answer_text'))->true();
		verify($this->model->validate('question_id'))->true();
	}

	public function testCreate_6()
	{
		$this->model->answer_text = str_repeat(' ', 8192);
		$this->model->question_id = $this->question->id;

		verify($this->model->validate('answer_text'))->false();
		verify($this->model->validate('question_id'))->true();
	}

	public function testCreate_7()
	{
		$this->model->answer_text = str_repeat("\n", 8192);
		$this->model->question_id = $this->question->id;

		verify($this->model->validate('answer_text'))->false();
		verify($this->model->validate('question_id'))->true();
	}

	public function testCreate_8()
	{
		$this->model->answer_text = str_repeat(" ", 8192 + 1);
		$this->model->question_id = $this->question->id;

		verify($this->model->validate('answer_text'))->false();
		verify($this->model->validate('question_id'))->true();
	}

	public function testCreate_9()
	{
		$this->model->answer_text = str_repeat(" ", 8192 + 1) . '1';
		$this->model->question_id = $this->question->id;

		verify($this->model->validate('answer_text'))->false();
		verify($this->model->validate('question_id'))->true();
	}

	public function testCreate_10()
	{
		$html = Html::tag('span', '');
		$this->model->answer_text = $html;
		$this->model->question_id = $this->question->id;

		verify($this->model->validate('answer_text'))->false();
		verify($this->model->validate('question_id'))->true();
	}

	public function testCreate_11()
	{
		$html = Html::tag('span', ' ');
		$this->model->answer_text = $html;
		$this->model->question_id = $this->question->id;

		verify($this->model->validate('answer_text'))->false();
		verify($this->model->validate('question_id'))->true();
	}

	public function testCreate_12()
	{
		$html = Html::tag('span', '1');
		$this->model->answer_text = $html;
		$this->model->question_id = $this->question->id;

		verify($this->model->validate('answer_text'))->true();
		verify($this->model->validate('question_id'))->true();
	}

}
