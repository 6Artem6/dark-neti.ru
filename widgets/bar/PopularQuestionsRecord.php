<?php

namespace app\widgets\bar;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\Question;
use app\models\helpers\HtmlHelper;


class PopularQuestionsRecord extends Widget
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
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['card-body', 'position-relative', 'py-2']]);

		$output .= Html::beginTag('div', ['class' => 'w-100']);
		$output .= Html::a($this->model->question_title,
			$this->model->getRecordLink(),
			['class' => ['text-break', 'text-secondary', 'link-primary']]
		);
		$output .= Html::endTag('div');

		$item_list = [];
		$text = HtmlHelper::getIconText(Yii::t('app', 'Ответов:')) .
			HtmlHelper::getCountText($this->model->answer_count);
		$title = Html::tag('span', $text, [
			'class' => ['bi', 'bi-chat-left-text-fill', 'bi_icon', 'text-secondary'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Ответов дано на вопрос всего')
		]);
		$item_list[] = Html::a($title,
			$this->model->getAnswersLink(),
			['class' => ['link-secondary']]
		);

		$text = HtmlHelper::getIconText(Yii::t('app', 'Подписчиков:')) .
				HtmlHelper::getCountText($this->model->followers);
		$title = ($this->model->followers ?
			Yii::t('app', 'На вопрос подписано пользователей: {followers}', ['followers' => $this->model->followers]) :
			Yii::t('app', 'На вопрос пока не было подписок')
		);
		$title = Html::tag('span', $text, [
			'class' => ['bi', 'bi-bell-fill', 'bi_icon', 'text-secondary'],
			'data-toggle' => 'tooltip',
			'title' => $title
		]);
		$item_list[] = Html::a($title,
			$this->model->getFollowersLink(),
			['class' => ['link-secondary']]
		);
		if ($this->model->is_closed) {
			$title = HtmlHelper::getIconText(Yii::t('app', 'Закрыт'));
			$item_list[] = Html::tag('span', $title, [
				'class' => ['badge', 'rounded-pill', 'bg-success', 'fw-bold', 'bi', 'bi-check-circle-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' =>  Yii::t('app', "Вопрос закрыт.\nОтветы больше не принимаются.")
			]);
		} elseif (!$this->model->is_closed and $this->model->end_datetime) {
			$item_list[] = Html::tag('span', $this->model->end_datetime, [
				'class' => ['time', 'pe-1'],
				'style' => ['display' => 'none'],
				'data-time' => date('Y-m-d H:i:s', strtotime($this->model->end_datetime)),
				'title' => Yii::t('app', 'Время сдачи задания: {datetime}', ['datetime' => $this->model->endTimeFull])
			]);
		}

		$output .= Html::ul($item_list, [
			'class' => ['nav', 'nav-stack', 'gap-0', 'align-items-center', 'w-100'],
			'style' => ['font-size' => '11px'],
			'item' => function($item, $index) {
				$return = '';
				if ($index) {
					$class = null;
					if (str_contains($item, 'time')) {
						$class = 'time-dot';
					}
					$return .= HtmlHelper::circle($class);
				}
				$return .= Html::tag('li', $item, ['class' => ['nav-item']]);
				return $return;
			},
		]);

		$output .= Html::endTag('div');

		return $output;
	}

}
