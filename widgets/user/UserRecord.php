<?php

namespace app\widgets\user;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\user\User;
use app\models\helpers\HtmlHelper;


class UserRecord extends Widget
{

	public User $model;
	public bool $show_info = true;
	public ?int $question_count = null;
	public ?int $answer_count = null;
	public ?int $answer_helped_count = null;

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
		$user_data = $this->model->data;
		$badge_data = $user_data->badgeData;
		if (is_null($this->question_count)) {
			$this->question_count = $badge_data->question_count;
		}
		if (is_null($this->answer_count)) {
			$this->answer_count = $badge_data->answer_count;
		}
		if (is_null($this->answer_helped_count)) {
			$this->answer_helped_count = $badge_data->answer_helped_count;
		}

		$output = '';

		$output .= Html::beginTag('div', ['class' => ['col']]);
		$output .= Html::beginTag('div', ['class' => ['card', 'h-100']]);

		$output .= Html::beginTag('div', ['class' => ['card-header', 'py-1', 'px-2']]);

		$output .= Html::beginTag('div', ['class' => ['d-flex', 'align-items-center']]);
		$output .= Html::beginTag('div', [
			'class' => ['p-1'],
			'style' => ['min-width' => '45px', 'width' => '45px']
		]);
		$output .= $this->model->getAvatar('sm', $this->show_info);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['p-2']]);
		$output .= Html::a($this->model->shortname, $this->model->getPageLink(), ['class' => ['mt-3', 'h6']]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['card-body', 'py-2']]);

		$output .= Html::beginTag('div', ['class' => ['user-data', 'mb-0', 'small']]);
		$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-warning', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Задал вопросов:')) .
			HtmlHelper::getCountText($this->question_count);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-question-circle-fill', 'bi_icon']]);
		$output .= Html::endTag('span');

		$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-info', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Дал ответов:')) .
			HtmlHelper::getCountText($this->answer_count);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-chat-left-text-fill', 'bi_icon']]);
		$output .= Html::endTag('span');

		$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-success', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Помогло ответов:')) .
			HtmlHelper::getCountText($this->answer_helped_count);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-check-circle-fill', 'bi_icon']]);
		$output .= Html::endTag('span');

		$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-danger', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Рейтинг:')) .
			HtmlHelper::getCountText($user_data->rate_sum);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-star-fill', 'bi_icon']]);
		$output .= Html::endTag('span');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['card-footer', 'py-3']]);
		$output .= Html::beginTag('div', ['class' => ['w-100']]);

		if ($user_data->isSelf) {
			$title = HtmlHelper::getIconText(Yii::t('app', 'Подписчиков:')) .
			HtmlHelper::getCountText($user_data->followers, true);
			$output .= Html::tag('span', $title, [
				'class' => [
					'btn', 'btn-sm', 'btn-outline-light', 'text-secondary', 'w-100', 'rounded-pill',
					'bi', 'bi-bell', 'bi_icon', 'me-2'
				],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Количество ваших подписчиков')
			]);
		} elseif ($user_data->isFollowed) {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Вы подписаны')) .
				HtmlHelper::getCountText($user_data->followers, true);
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell-fill', 'bi_icon']]);
			$output .= HtmlHelper::actionButton($title, 'unfollow-user', $this->model->id, [
				'class' => ['btn', 'btn-sm', 'btn-light', 'text-secondary', 'w-100', 'rounded-pill'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Отписаться от пользователя')
			]);
		} else {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Подписаться')) .
				HtmlHelper::getCountText($user_data->followers, true);
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell', 'bi_icon']]);
			$output .= HtmlHelper::actionButton($title, 'follow-user', $this->model->id, [
				'class' => ['btn', 'btn-sm', 'btn-outline-light', 'text-secondary', 'w-100', 'rounded-pill'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Подписаться на пользователя')
			]);
		}
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

}
