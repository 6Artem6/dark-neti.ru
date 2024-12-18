<?php

namespace tests\unit\models;

use \Yii;
use yii\helpers\Html;
use app\models\question\Question;
use app\models\user\User;


class QuestionTest extends \Codeception\Test\Unit
{
	protected $model;

    protected function _before()
    {
    	$user = User::findOne(1);
    	Yii::$app->user->login($user);
    	$this->model = new Question(['scenario' => Question::SCENARIO_CREATE]);
    }

    protected function _after()
    {
    	if (!$this->model->hasErrors()) {

    	}
    }

	public function testCreate_1()
	{
		$this->model->question_title = 'Как решать РГР';
		$this->model->type_id = 1;
		$this->model->faculty_id = 1;
		$this->model->discipline_name = 'Экономика';
		$this->model->teachers = ['Гордячкова О.В.'];
		$this->model->tags = ['Задача'];
		$this->model->end_datetime = '11.09.2022 18:00';
		$this->model->question_text = 'Как нужно решить эту РГР?';

		verify($this->model->save())->true();
		verify($this->model->validate('question_title'))->true();
		verify($this->model->validate('type_id'))->true();
		verify($this->model->validate('faculty_id'))->true();
		verify($this->model->validate('discipline'))->true();
		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();
		verify($this->model->validate('end_datetime'))->true();
		verify($this->model->validate('question_text'))->true();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_2()
	{
		$this->model->question_title = null;
		$this->model->type_id = null;
		$this->model->faculty_id = null;
		$this->model->discipline_name = null;
		$this->model->teachers = null;
		$this->model->tags = null;
		$this->model->end_datetime = null;
		$this->model->question_text = null;

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->false();
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify($this->model->validate('discipline'))->false();
		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();
		verify($this->model->validate('end_datetime'))->true();
		verify($this->model->validate('question_text'))->false();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_3()
	{
		$this->model->question_title = '';
		$this->model->type_id = 0;
		$this->model->faculty_id = 0;
		$this->model->discipline_name = '';
		$this->model->teachers = '';
		$this->model->tags = '';
		$this->model->end_datetime = '';
		$this->model->question_text = '';

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->false();
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify($this->model->validate('discipline'))->false();
		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();
		verify($this->model->validate('end_datetime'))->true();
		verify($this->model->validate('question_text'))->false();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_4()
	{
		$this->model->question_title = '';
		$this->model->type_id = 0;
		$this->model->faculty_id = 0;
		$this->model->discipline_name = '';
		$this->model->teachers = [''];
		$this->model->tags = [''];
		$this->model->end_datetime = '1';
		$this->model->question_text = '';

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->false();
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify($this->model->validate('discipline'))->false();
		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();
		verify($this->model->validate('end_datetime'))->true();
		verify($this->model->validate('question_text'))->false();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_5()
	{
		$this->model->question_title = '1234';
		$this->model->type_id = 1;
		$this->model->faculty_id = 1;
		$this->model->discipline_name = '1';
		$this->model->teachers = ['1'];
		$this->model->tags = ['1'];
		$this->model->end_datetime = '2022-01-01 06:30';
		$this->model->question_text = '123456789';

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->false();
		verify($this->model->validate('type_id'))->true();
		verify($this->model->validate('faculty_id'))->true();
		verify($this->model->validate('discipline'))->false();
		verify($this->model->validate('teachers'))->false();
		verify($this->model->validate('tags'))->false();
		verify($this->model->validate('end_datetime'))->true();
		verify($this->model->validate('question_text'))->false();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_6()
	{
		$this->model->question_title = '12345';
		$this->model->type_id = 1;
		$this->model->faculty_id = 1;
		$this->model->discipline_name = '12';
		$this->model->teachers = ['12'];
		$this->model->tags = ['12'];
		$this->model->end_datetime = '2022-01-01 06:30:00';
		$this->model->question_text = '1234567890';

		verify($this->model->save())->true();
		verify($this->model->validate('question_title'))->true();
		verify($this->model->validate('type_id'))->true();
		verify($this->model->validate('faculty_id'))->true();
		verify($this->model->validate('discipline'))->true();
		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();
		verify($this->model->validate('end_datetime'))->true();
		verify($this->model->validate('question_text'))->true();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_7()
	{
		$this->model->question_title = str_repeat('.', 512);
		$this->model->type_id = 10;
		$this->model->faculty_id = 10;
		$this->model->discipline_name = str_repeat('.', 50);
		$this->model->teachers = [str_repeat('.', 50)];
		$this->model->tags = [str_repeat('.', 20)];
		$this->model->end_datetime = '2022-01-01 06:30:00:20';
		$this->model->question_text = str_repeat('.', 8192);

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->true();
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->true();
		verify($this->model->validate('discipline'))->true();
		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();
		// verify($this->model->validate('end_datetime'))->false();
		verify($this->model->validate('question_text'))->true();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_8()
	{
		$this->model->question_title = str_repeat('.', 512 + 1);
		$this->model->type_id = 100;
		$this->model->faculty_id = 100;
		$this->model->discipline_name = str_repeat('.', 50 + 1);
		$this->model->teachers = [str_repeat('.', 50 + 1)];
		$this->model->tags = [str_repeat('.', 20 + 1)];
		$this->model->end_datetime = '2022-01-01 06';
		$this->model->question_text = str_repeat('.', 8192 + 1);

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->false();
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify($this->model->validate('discipline'))->false();
		verify($this->model->validate('teachers'))->false();
		verify($this->model->validate('tags'))->false();
		// verify($this->model->validate('end_datetime'))->false();
		verify($this->model->validate('question_text'))->false();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_9()
	{
		$this->model->question_title = str_repeat('.', 512 * 2);
		$this->model->type_id = 100;
		$this->model->faculty_id = 100;
		$this->model->discipline_name = str_repeat('.', 50  * 2);
		$this->model->teachers = [str_repeat('.', 50  * 2)];
		$this->model->tags = [str_repeat('.', 20  * 2)];
		$this->model->end_datetime = '2022-01-01';
		$this->model->question_text = str_repeat('.', 8192 * 2);

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->false();
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify($this->model->validate('discipline'))->false();
		verify($this->model->validate('teachers'))->false();
		verify($this->model->validate('tags'))->false();
		// verify($this->model->validate('end_datetime'))->false();
		verify($this->model->validate('question_text'))->false();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_10()
	{
		$this->model->question_title = str_repeat(' ', 512);
		$this->model->type_id = 'a';
		$this->model->faculty_id = 'a';
		$this->model->discipline_name = str_repeat(' ', 50);
		$this->model->teachers = [str_repeat(' ', 50)];
		$this->model->tags = [str_repeat(' ', 20)];
		$this->model->end_datetime = 'a';
		$this->model->question_text = str_repeat(' ', 8192);

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->false();
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify($this->model->validate('discipline'))->false();
		verify(count($this->model->teachers))->equals(0);
		verify(count($this->model->tags))->equals(0);
		// verify($this->model->validate('end_datetime'))->false();
		verify($this->model->validate('question_text'))->false();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_11()
	{
		$this->model->question_title = str_repeat("\n", 512);
		$this->model->type_id = "\n";
		$this->model->faculty_id = "\n";
		$this->model->discipline_name = str_repeat("\n", 50);
		$this->model->teachers = [str_repeat("\n", 50)];
		$this->model->tags = [str_repeat("\n", 20)];
		$this->model->end_datetime = "\n";
		$this->model->question_text = str_repeat("\n", 8192);

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->false();
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify($this->model->validate('discipline'))->false();
		verify(count($this->model->teachers))->equals(0);
		verify(count($this->model->tags))->equals(0);
		// verify($this->model->validate('end_datetime'))->false();
		verify($this->model->validate('question_text'))->false();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_12()
	{
		$text = '1';
		$html = Html::tag('span', $text);
		$this->model->question_title = $html;
		$this->model->type_id = $html;
		$this->model->faculty_id = $html;
		$this->model->discipline_name = $html;
		$this->model->teachers = [$html];
		$this->model->tags = [$html];
		$this->model->end_datetime = $html;
		$this->model->question_text = $html;

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->false();
		verify($this->model->question_title)->equals($text);
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify($this->model->validate('discipline'))->false();
		verify(strlen($this->model->teachers[0]))->equals(strlen($text));
		verify(strlen($this->model->tags[0]))->equals(strlen($text));
		// verify($this->model->validate('end_datetime'))->false();
		verify($this->model->validate('question_text'))->false();
		verify($this->model->question_text)->equals($text);
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_13()
	{
		$text = '1234567890';
		$html = Html::tag('span', $text);
		$this->model->question_title = $html;
		$this->model->type_id = $html;
		$this->model->faculty_id = $html;
		$this->model->discipline_name = $html;
		$this->model->teachers = [$html];
		$this->model->tags = [$html];
		$this->model->end_datetime = $html;
		$this->model->question_text = $html;

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->true();
		verify($this->model->question_title)->equals($text);
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify($this->model->validate('discipline'))->true();
		verify(strlen($this->model->teachers[0]))->equals(strlen($text));
		verify(strlen($this->model->tags[0]))->equals(strlen($text));
		// verify($this->model->validate('end_datetime'))->false();
		verify($this->model->validate('question_text'))->true();
		verify($this->model->question_text)->equals($text);
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_15()
	{
		$text = '1234567890';
		$this->model->question_title = str_repeat(' ', 512) . $text;
		$this->model->type_id = 'a';
		$this->model->faculty_id = 'a';
		$this->model->discipline_name = str_repeat(' ', 50) . $text;
		$this->model->teachers = [str_repeat(' ', 50) . $text];
		$this->model->tags = [str_repeat(' ', 20) . $text];
		$this->model->end_datetime = 'a';
		$this->model->question_text = str_repeat(' ', 8192) . $text;

		verify($this->model->save())->false();
		verify($this->model->validate('question_title'))->true();
		verify($this->model->validate('type_id'))->false();
		verify($this->model->validate('faculty_id'))->false();
		verify(strlen($this->model->discipline_name))->equals(strlen($text));
		verify(strlen($this->model->teachers[0]))->equals(strlen($text));
		verify(strlen($this->model->tags[0]))->equals(strlen($text));
		// verify($this->model->validate('end_datetime'))->false();
		verify($this->model->validate('question_text'))->true();
		verify($this->model->validate('question_datetime'))->true();
		verify($this->model->validate('user_id'))->true();
	}

	public function testCreate_16()
	{
		$text = '1234567890';
		$arr = [$text, $text, $text, $text, $text, $text, $text, $text];
		$this->model->teachers = $arr;
		$this->model->tags = $arr;

		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();

		verify(count($this->model->teachers))->equals(3);
		verify(count($this->model->tags))->equals(5);
	}

	public function testCreate_17()
	{
		$text = '   ';
		$arr = [$text, $text, $text, $text, $text, $text, $text, $text];
		$this->model->teachers = $arr;
		$this->model->tags = $arr;

		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();

		verify(count($this->model->teachers))->equals(0);
		verify(count($this->model->tags))->equals(0);
	}

	public function testCreate_18()
	{
		$empty = '   ';
		$text = '1234567890';
		$arr = [$empty, $empty, $text, $text, $text, $text, $text, $text];
		$this->model->teachers = $arr;
		$this->model->tags = $arr;

		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();

		verify(count($this->model->teachers))->equals(3);
		verify(count($this->model->tags))->equals(5);
	}

	public function testCreate_19()
	{
		$empty = '   ';
		$text = '1234567890';
		$arr = [$text, $text, $text, $empty, $empty];
		$this->model->teachers = $arr;
		$this->model->tags = $arr;

		verify($this->model->validate('teachers'))->true();
		verify($this->model->validate('tags'))->true();

		verify(count($this->model->teachers))->equals(3);
		verify(count($this->model->tags))->equals(3);
	}

}
