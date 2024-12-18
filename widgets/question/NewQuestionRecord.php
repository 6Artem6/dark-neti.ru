<?php

namespace app\widgets\question;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\Question;
use app\models\helpers\HtmlHelper;


class NewQuestionRecord extends Widget
{

	public Question $model;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if ($this->model->isNewRecord) {
			return false;
		}
		return true;
	}

	public function run()
	{
		$circle = HtmlHelper::circle();
		$output = "";

		$output .= Html::beginTag('div', ['class' => ["col-12"]]);
		$output .= Html::tag('h5',
			Html::a($this->model->question_title, $this->model->getRecordLink(), ['class' => ['text-secondary', 'link-primary']]),
			['class' => 'mt-0']
		);
		$output .= Html::tag('p',
			Html::a($this->model->shortText, $this->model->getRecordLink(), ['class' => ['text-secondary', 'link-primary']]),
			['class' => ['bg-light', 'rounded', 'text-justify', 'mt-0', 'p-1']]
		);

		$item_list = [];

		$title = HtmlHelper::getIconText(Yii::t('app', 'Задан:')) . $this->model->timeElapsed;
		$item_list[] = Html::tag('span', $title, [
			'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Вопрос задан: {datetime}', ['datetime' => $this->model->datetime])
		]);

		$title = HtmlHelper::getIconText(Yii::t('app', 'Просмотров:')) . $this->model->views;
		$item_list[] = Html::tag('span', $title, [
			'class' => ['bi', 'bi-eye-fill', 'bi_icon'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Вопрос просмотрели раз: {views}', ['views' => $this->model->views])
		]);

		$text = HtmlHelper::getIconText(Yii::t('app', 'Ответов:')) .
			($this->model->answer_count ? $this->model->answer_count : Yii::t('app', 'нет'));
		$title_text = ($this->model->answer_count ?
			Yii::t('app', 'На вопрос ответили раз: {followers}', ['followers' => $this->model->followers]) :
			Yii::t('app', 'На вопрос пока не дали ответов')
		);
		$title = Html::tag('span', $text, [
			'class' => ['bi', 'bi-chat-left-text-fill', 'bi_icon '],
			'data-toggle' => 'tooltip',
			'title' => $title_text
		]);
		$item_list[] = Html::a($title,
			$this->model->getAnswersLink(),
			['class' => ['text-secondary', 'link-primary']]
		);

		$text = HtmlHelper::getIconText(Yii::t('app', 'Подписчиков:')) .
			($this->model->followers ? $this->model->followers : Yii::t('app', 'нет'));
		$title_text = ($this->model->followers ?
			Yii::t('app', 'На вопрос подписалось пользователей: {followers}', ['followers' => $this->model->followers]) :
			Yii::t('app', 'На вопрос пока не было подписок')
		);
		$title = Html::tag('span', $text, [
			'class' => ['bi', 'bi-bell-fill', 'bi_icon'],
			'data-toggle' => 'tooltip',
			'title' => $title_text
		]);
		$item_list[] = Html::a($title,
			$this->model->getFollowersLink(),
			["class" => ['text-secondary', 'link-primary']]
		);

		if ($this->model->is_closed) {
			$title = HtmlHelper::getIconText(Yii::t('app', 'Закрыт'));
			$item_list[] =  Html::tag('span', $title, [
				'class' => ['badge', 'rounded-pill', 'bg-success', 'fw-bold', 'bi', 'bi-check-circle-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' =>  Yii::t('app', "Вопрос закрыт.\nОтветы больше не принимаются.")
			]);
		} elseif (!$this->model->is_closed and $this->model->end_datetime) {
			$item_list[] = Html::tag('span', $this->model->end_datetime, [
				'class' => ['time', 'pe-1'],
				'style' => ['display' => 'none'],
				'data-time' => date('Y-m-d H:i:s', strtotime($this->model->end_datetime)),
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Время сдачи задания: {datetime}', ['datetime' => $this->model->end_datetime])
			]);
		}

		$output .= Html::ul($item_list, [
			'tag' => 'p',
			'class' => ['text-secondary'],
			'style' => ['font-size' => '12px'],
			'item' => function($item, $index) {
				$return = "";
				if ($index) {
					$class = null;
					if (str_contains($item, 'time')) {
						$class = 'time-dot';
					}
					$return .= HtmlHelper::circle($class);
				}
				$return .= $item;
				return $return;
			},
		]);

		$output .= Html::endTag('div');
		return $output;
	}

}
