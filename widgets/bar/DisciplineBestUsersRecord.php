<?php

namespace app\widgets\bar;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\user\User;
use app\models\helpers\HtmlHelper;


class DisciplineBestUsersRecord extends Widget
{

	public User $model;
	public int $answer_count = 0;
	public int $helped_count = 0;

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
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['card-body', 'position-relative', 'py-2']]);

		$output .= Html::beginTag('div', ['class' => 'w-100']);

		$output .= Html::beginTag('div', ['class' => 'd-flex']);
		$output .= Html::beginTag('div', ['class' => 'p-2']);
		$output .= Html::beginTag('div', ['class' => 'avatar']);
		$output .= $this->model->getAvatar('md');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['px-1', 'align-items-center', 'my-auto']]);
		$output .= Html::beginTag('div', ['class' => ['mb-0', 'small']]);
		$output .= Html::a($this->model->name, $this->model->getPageLink(), ['class' => ['h6', 'mb-0', 'text-break']]);
		$output .= Html::endTag('div');

		$item_list = [];

		$text = HtmlHelper::getCountText($this->answer_count);
		$item_list[] = Html::tag('span', $text, [
			'class' => ['bi', 'bi-chat-left-text-fill', 'bi_icon', 'text-secondary'],
			'data-toggle' => 'tooltip',
			'title' =>  Yii::t('app', 'Количество ответов пользователя по предмету за выбранный промежуток')
		]);

		$text = HtmlHelper::getCountText($this->helped_count);
		$item_list[] = Html::tag('span', $text, [
			'class' => ['bi', 'bi-check-circle-fill', 'bi_icon', 'text-secondary'],
			'data-toggle' => 'tooltip',
			'title' =>  Yii::t('app', 'Количество решивших вопрос ответов пользователя по предмету за выбранный промежуток')
		]);

		$text = HtmlHelper::getCountText($user_data->followers);
		if ($user_data->isSelf) {
			$title_text = Yii::t('app', 'Количество ваших подписчиков');
		} elseif (!$user_data->followers) {
			$title_text = Yii::t('app', 'На пользователя пока не было подписок');
		} else {
			$title_text = Yii::t('app', 'На пользователя подписано человек: {followers}', ['followers' => $user_data->followers]);
		}
		$item_list[] = Html::tag('span', $text, [
			'class' => ['bi', 'bi-bell-fill', 'bi_icon', 'text-secondary'],
			'data-toggle' => 'tooltip',
			'title' =>  $title_text
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

		$output .= Html::beginTag('div', ['class' => ['px-1', 'follow-button-div']]);
		if ($user_data->isSelf) {
			$output .= Html::button(null, [
				'class' => ['btn', 'btn-outline-light', 'btn-sm', 'text-secondary', 'rounded-pill', 'px-2', 'bi', 'bi-bell-fill', 'bi_icon', 'disabled'],
			]);
		} elseif (!$user_data->isFollowed) {
			$output .= HtmlHelper::actionButton(null, 'follow-user', $this->model->id, [
				'class' => ['btn', 'btn-outline-light', 'btn-sm', 'text-secondary', 'rounded-pill', 'px-2', 'bi', 'bi-bell', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Подписаться на пользователя')
			]);
		} else {
			$output .= HtmlHelper::actionButton(null, 'unfollow-user', $this->model->id, [
				'class' => ['btn', 'btn-light', 'btn-sm', 'text-secondary', 'rounded-pill', 'px-2', 'bi', 'bi-bell-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Отписаться от пользователя')
			]);
		}
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

}
