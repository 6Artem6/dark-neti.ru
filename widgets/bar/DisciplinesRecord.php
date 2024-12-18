<?php

namespace app\widgets\bar;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\edu\Discipline;
use app\models\helpers\HtmlHelper;


class DisciplinesRecord extends Widget
{

	public Discipline $model;
	public int $question_count = 0;
	public int $question_helped_count = 0;
	public bool $show_period_count = true;

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
		$output .= Html::beginTag('div', ['class' => 'd-flex']);

		$output .= Html::beginTag('div', ['class' => 'p-2']);
		$output .= Html::beginTag('div', ['class' => 'avatar']);
		$output .= Html::a(
			Html::img($this->model->getImgLink(), ['class' => ['rounded-circle', 'w-100', 'bg-white']]),
			$this->model->getRecordLink()
		);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['px-1', 'align-items-center', 'my-auto']]);
		$output .= Html::beginTag('div', ['class' => ['mb-0', 'small']]);
		$output .= Html::a($this->model->discipline_name, $this->model->getRecordLink(), ['class' => ['h6', 'mb-0', 'text-break']]);
		$output .= Html::endTag('div');

		$item_list = [];

		$text = HtmlHelper::getCountText($this->model->question_count);
		$item_list[] = Html::tag('span', $text, [
			'class' => ['bi', 'bi-question-circle-fill', 'bi_icon', 'text-secondary'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Вопросов задано по предмету за всё время')
		]);

		if ($this->show_period_count) {
			$text = HtmlHelper::getCountText($this->question_count);
			$item_list[] = Html::tag('span', $text, [
				'class' => ['bi', 'bi-question-circle', 'bi_icon', 'text-secondary'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Вопросов задано по предмету за выбранный период')
			]);
		}

		if ($this->show_period_count) {
			$text = HtmlHelper::getCountText($this->question_helped_count);
			$item_list[] = Html::tag('span', $text, [
				'class' => ['bi', 'bi-check-circle-fill', 'bi_icon', 'text-secondary'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Решений дано по предмету за выбранный период')
			]);
		} else {
			$text = HtmlHelper::getCountText($this->model->question_helped_count);
			$item_list[] = Html::tag('span', $text, [
				'class' => ['bi', 'bi-check-circle-fill', 'bi_icon', 'text-secondary'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Решений дано по предмету за всё время')
			]);
		}

		$text = HtmlHelper::getCountText($this->model->followers);
		$title_text = ($this->model->followers ?
			Yii::t('app', 'На предмет подписано человек: {followers}', ['followers' => $this->model->followers]) :
			Yii::t('app', 'На предмет пока не было подписок')
		);
		$item_list[] = Html::tag('span', $text, [
			'class' => ['bi', 'bi-bell-fill', 'bi_icon', 'text-secondary'],
			'data-toggle' => 'tooltip',
			'title' => $title_text
		]);

		$count = count($item_list);
		$output .= Html::ul($item_list, [
			'tag' => 'div',
			'class' => ['d-flex', 'nav', 'small', 'align-items-center'],
			'item' => function ($item, $index) use ($count) {
				$return = $item;
				if ($index < $count - 1) {
					$return .= HtmlHelper::circle();
				}
				return Html::tag('div', $return);
			},
		]);

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['px-1', 'pt-1', 'follow-button-div']]);
		if (!$this->model->isFollowed) {
			$output .= HtmlHelper::actionButton(null, 'follow-discipline', $this->model->id, [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'btn-sm', 'rounded-pill', 'px-2', 'bi', 'bi-bell', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Подписаться на предмет')
			]);
		} else {
			$output .= HtmlHelper::actionButton(null, 'unfollow-discipline', $this->model->id, [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'btn-sm', 'rounded-pill', 'px-2', 'bi', 'bi-bell-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Отписаться от предмета')
			]);
		}
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

}
