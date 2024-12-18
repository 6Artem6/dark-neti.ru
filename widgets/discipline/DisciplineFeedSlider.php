<?php

namespace app\widgets\discipline;

use Yii;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\Url;

use app\models\edu\Discipline;
use app\models\helpers\HtmlHelper;

use app\assets\widgets\SliderAsset;


class DisciplineFeedSlider extends Widget
{

	public $list;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if ($this->list) {
			foreach ($this->list as $record) {
				if (!($record instanceof Discipline)) {
					return false;
				}
				if ($record->isNewRecord) {
					return false;
				}
			}
		}
		SliderAsset::register($this->view);
		return true;
	}

	public function run()
	{
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['discipline-slider-wrapper', 'p-2']]);
		$output .= Html::beginTag('div', [
			'id' => 'slider',
			'class' => ['slider'],
			'style' => ['display' => 'none'],
		]);
		if ($this->list) {
			foreach ($this->list as $record) {
				$output .= Html::beginTag('div');
				$output .= Html::beginTag('div', [
					'class' => [
						'card', 'card-overlay-bottom', 'border-0', 'position-relative', 'feed-discipline-image'
					],
					'style' => [
						'background-image' => "url(" . $record->imgLink .")",
						'background-color' => 'white',
					],
				]);
				if ($record->isFollowed) {
					$output .= HtmlHelper::actionButton(null, 'unfollow-discipline', $record->id, [
						'class' => [
							'btn', 'btn-white-soft', 'position-absolute', 'translate-middle', 'bg-white', 'rounded', 'p-1',
							'bi', 'bi-bell-fill', 'discipline-bell'
						],
						'data-toggle' => 'tooltip',
						'title' => Yii::t('app', 'Отписаться от предмета')
					]);
				} else {
					$output .= HtmlHelper::actionButton(null, 'follow-discipline', $record->id, [
						'class' => [
							'btn', 'btn-white-soft', 'position-absolute', 'translate-middle', 'bg-white', 'rounded', 'p-1',
							'bi', 'bi-bell', 'discipline-bell'
						],
						'data-toggle' => 'tooltip',
						'title' => Yii::t('app', 'Подписаться на предмет')
					]);
				}

				$output .= Html::beginTag('div', ['class' => ['card-img-overlay', 'd-flex', 'align-items-center', 'p-2']]);
				$output .= Html::beginTag('div', ['class' => ['w-100', 'mt-auto']]);
				$output .= Html::a($record->name,
					Url::to(['feed/index', 'discipline' => $record->name]),
					['class' => ['stretched-link', 'text-white', 'small']]
				);
				$output .= Html::endTag('div');
				$output .= Html::endTag('div');
				$output .= Html::endTag('div');
				$output .= Html::endTag('div');
			}
		}
		$output .= Html::beginTag('div');
		$output .= Html::beginTag('div', [
			'class' => [
				'card', 'border', 'border-2', 'border-dashed', 'shadow-none',
				'd-flex', 'align-items-center', 'justify-content-center', 'text-center',
				'feed-discipline'
			]
		]);
		$output .= Html::a(
			Html::tag('i', null, ['class' => "fa-solid fa-plus"]),
			['/discipline'],
			['class' => "stretched-link btn btn-light rounded-circle icon-md"]
		);
		$output .= Html::tag('h6', Yii::t('app', 'Все<br>предметы'), ['class' => ['mt-2', 'mb-0', 'small']]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		return $output;
	}

}
