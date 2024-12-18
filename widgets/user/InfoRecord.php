<?php

namespace app\widgets\user;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\helpers\HtmlHelper;


class InfoRecord extends Widget
{

	public array $user_data;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if (empty($this->user_data)) {
			return false;
		}
		return true;
	}

	public function run()
	{
		$circle = HtmlHelper::circle();

		$output = '';

		$output .= Html::beginTag('div', [
			'class' => ['position-absolute', 'top-0', 'start-0'],
			'data-username' => $this->user_data['username'],
		]);
		$output .= Html::beginTag('div', ['class' => ['card', 'border', 'shadow-md', 'p-2']]);

		$output .= Html::beginTag('div', ['class' => ['d-flex', 'align-items-center']]);
		$output .= Html::beginTag('div', [
			'class' => ['p-1'],
			'style' => ['min-width' => '45px', 'max-width' => '60px']
		]);
		$output .= $this->user_data['avatar'];
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => 'flex-grow-1']);
		$output .= Html::beginTag('h6', ['class' => ['pt-2', 'text-center']]);
		$output .= Html::a($this->user_data['name'], $this->user_data['link'], ['class' => ['fw-bold']]);
		$output .= Html::endTag('h6');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');


		$output .= Html::beginTag('div', ['class' => ['user-online-data']]);
		$output .= $this->user_data['online_status'];
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div');
		$output .= Html::tag('hr', null, ['class' => 'my-2']);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['user-data', 'px-2']]);
		$output .= Html::beginTag('span', ['class' => ['my-0', 'text-warning', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Задал вопросов:')) .
			HtmlHelper::getCountText($this->user_data['question_count']);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-question-circle-fill', 'bi_icon']]);
		$output .= Html::endTag('span');

		$output .= Html::beginTag('span', ['class' => ['my-0', 'text-info', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Дал ответов:')) .
			HtmlHelper::getCountText($this->user_data['answer_count']);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-chat-left-text-fill', 'bi_icon']]);
		$output .= Html::endTag('span');

		$output .= Html::beginTag('span', ['class' => ['my-0', 'text-success', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Помогло ответов:')) .
			HtmlHelper::getCountText($this->user_data['answer_helped_count']);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-check-circle-fill', 'bi_icon']]);
		$output .= Html::endTag('span');

		$output .= Html::beginTag('span', ['class' => ['my-0', 'text-danger', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Рейтинг:')) .
			HtmlHelper::getCountText($this->user_data['rate_sum']);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-star-fill', 'bi_icon']]);
		$output .= Html::endTag('span');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div');
		$output .= Html::tag('hr', null, ['class' => 'my-2']);
		$output .= Html::endTag('div');

		if ($this->user_data['disciplines']) {
			$output .= Html::beginTag('div', ['class' => ['px-2']]);
			$output .= Html::beginTag('p', ['class' => ['my-0', 'user-discipline-data']]);
			$output .= Yii::t('app', 'Основные предметы:');
			$output .= Html::endTag('p');
			foreach ($this->user_data['disciplines'] as $discipline) {
				$output .= Html::beginTag('p', ['class' => ['my-0', 'user-discipline-data']]);
				$output .= Html::a($discipline->name, $discipline->getPageLink(), ['class' => ['fw-bold']]);
				$output .= Html::endTag('p');
			}
			$output .= Html::endTag('div');

			$output .= Html::beginTag('div');
			$output .= Html::tag('hr', null, ['class' => 'my-2']);
			$output .= Html::endTag('div');
		}

		$output .= Html::beginTag('div', ['class' => ['user-badge-data', 'px-2']]);
		$output .= Html::tag('span', $this->user_data['badge_platinum_count'], [
			'class' => ['bi', 'bi_icon', 'bi-award', 'text-secondary'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Платиновых наград: {count}', ['count' => $this->user_data['badge_platinum_count']])
		]);
		$output .=  $circle;
		$output .= Html::tag('span', $this->user_data['badge_gold_count'], [
			'class' => ['bi', 'bi_icon', 'bi-award', 'text-warning'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Золотых наград: {count}', ['count' => $this->user_data['badge_gold_count']])
		]);
		$output .=  $circle;
		$output .= Html::tag('span', $this->user_data['badge_silver_count'], [
			'class' => ['bi', 'bi_icon', 'bi-award', 'text-light'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Серебряных наград: {count}', ['count' => $this->user_data['badge_silver_count']])
		]);
		$output .=  $circle;
		$output .= Html::tag('span', $this->user_data['badge_bronze_count'], [
			'class' => ['bi', 'bi_icon', 'bi-award', 'text-danger'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Бронзовых наград: {count}', ['count' => $this->user_data['badge_bronze_count']])
		]);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div');
		$output .= Html::tag('hr', null, ['class' => 'my-2']);
		$output .= Html::endTag('div');

		if ($this->user_data['self']) {
			$title = HtmlHelper::getIconText(Yii::t('app', 'Подписчиков:')) .
				HtmlHelper::getCountText($this->user_data['followers'], true);
			$output .= Html::tag('span', $title, [
				'class' => [
					'btn', 'btn-sm', 'btn-outline-light', 'text-secondary', 'w-100', 'rounded-pill',
					'bi', 'bi-bell', 'bi_icon', 'me-2'
				],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Количество ваших подписчиков')
			]);
		} elseif ($this->user_data['follow']) {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Вы подписаны'));
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell-fill', 'bi_icon']]);
			$output .= HtmlHelper::actionButton($title, 'unfollow-user', $this->user_data['id'], [
				'class' => ['btn', 'btn-sm', 'btn-light', 'text-secondary', 'w-100', 'rounded-pill'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Отписаться от пользователя')
			]);
		} else {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Подписаться'));
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell', 'bi_icon']]);
			$output .= HtmlHelper::actionButton($title, 'follow-user', $this->user_data['id'], [
				'class' => ['btn', 'btn-sm', 'btn-outline-light', 'text-secondary', 'w-100', 'rounded-pill'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Подписаться на пользователя')
			]);
		}

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

}
