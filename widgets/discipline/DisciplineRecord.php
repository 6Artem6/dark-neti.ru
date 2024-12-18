<?php

namespace app\widgets\discipline;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\edu\Discipline;
use app\models\helpers\HtmlHelper;


class DisciplineRecord extends Widget
{

	public Discipline $model;
	public bool $is_author = false;
	public bool $is_search = false;

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
		$class = null;
		if ($this->is_search) {
			$class = 'feed-discipline-search';
		}
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['col', $class]]);
		$output .= Html::beginTag('div', ['class' => ['card', 'h-100']]);

		$output .= Html::beginTag('div', ['class' => ['card-header', 'h-100', 'py-1', 'px-2', 'border-0']]);
		$output .= Html::beginTag('div', ['class' => ['d-flex', 'h-100', 'align-items-center']]);
		$output .= Html::beginTag('div', [
			'class' => ['p-1'],
			'style' => ['min-width' => '45px', 'width' => '45px']
		]);
		$output .= Html::a(
			Html::img($this->model->getImgLink(), ['class' => ['rounded-circle', 'w-100', 'discipline-image', 'bg-white']]),
			$this->model->getPageLink()
		);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['p-2']]);
		$output .= Html::a($this->model->name,
			$this->model->getPageLink(),
			['class' => ['h6', 'small', 'text-break']
		]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['card-body', 'py-0']]);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['card-footer', 'py-2', 'text-center']]);

		$output .= Html::beginTag('div', ['class' => ['discipline-data', 'mb-0', 'small']]);
		if ($this->is_author) {
			$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-warning', 'fw-bold']]);
			$title = HtmlHelper::getIconText(Yii::t('app', 'Ответов:')) .
				HtmlHelper::getCountText($this->model->filter_answer_count);
			$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-chat-left-text-fill', 'bi_icon', 'text-secondary']]);
			$output .= Html::endTag('span');

			$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-info', 'fw-bold']]);
			$title = HtmlHelper::getIconText(Yii::t('app', 'Вопросов:')) .
				HtmlHelper::getCountText($this->model->filter_question_count);
			$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-question-circle', 'bi_icon', 'text-secondary']]);
			$output .= Html::endTag('span');
		} else {
			$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-warning', 'fw-bold']]);
			$title = HtmlHelper::getIconText(Yii::t('app', 'Вопросов:')) .
				HtmlHelper::getCountText($this->model->question_count);
			$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-question-circle', 'bi_icon', 'text-secondary']]);
			$output .= Html::endTag('span');

			$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-info', 'fw-bold']]);
			$title = HtmlHelper::getIconText(Yii::t('app', 'Решённых:')) .
				HtmlHelper::getCountText($this->model->question_helped_count);
			$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-check-circle-fill', 'bi_icon', 'text-secondary']]);
			$output .= Html::endTag('span');
		}
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['card-footer']]);
		if ($this->model->isFollowed) {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Вы подписаны')) .
				HtmlHelper::getCountText($this->model->followers, true);
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell-fill', 'bi_icon']]);
			$output .= HtmlHelper::actionButton($title, 'unfollow-discipline', $this->model->id, [
				'class' => ['btn', 'btn-sm', 'btn-light', 'text-secondary', 'w-100', 'rounded-pill'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Отписаться от предмета')
			]);
		} else {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Подписаться')) .
				HtmlHelper::getCountText($this->model->followers, true);
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell', 'bi_icon']]);
			$output .= HtmlHelper::actionButton($title, 'follow-discipline', $this->model->id, [
				'class' => ['btn', 'btn-sm', 'btn-outline-light', 'text-secondary', 'w-100', 'rounded-pill'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Подписаться на предмет')
			]);
		}
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

}
