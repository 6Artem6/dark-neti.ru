<?php

namespace app\widgets\badge;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\badge\{Badge, UserBadgeData};


class BadgeRecord extends Widget
{

	public Badge $model;
	public UserBadgeData $data;

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
		$badge_count = $this->model->getLevelCount();
		$user_count = $this->data->getUserBadgeCount($this->model->type_id);
		$has_badge = ($user_count >= $badge_count);
		$name = '«'.$this->model->type->latin_name.'»';

		$classes = ['rounded-circle'];
		if (!$has_badge) {
			$classes[] = 'badge-inactive';
		}
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['col']]);
		$output .= Html::beginTag('div', ['class' => ['pb-4']]);

		$output .= Html::beginTag('div', ['class' => ['badge-div', 'badge-background']]);
		$output .= $this->model->getBadgeHtml();
		$output .= Html::beginTag('div', ['class' => $classes]);
		$output .= Html::beginTag('div', ['class' => ['rounded-circle', 'badge-layer']]);
		$output .= Html::beginTag('div', ['class' => ['badge-text']]);
		$output .= Html::tag('p', $name, ['class' => ['badge-info']]);
		$output .= Html::tag('p', $this->model->type->description, ['class' => ['badge-info']]);

		$output .= Html::tag('p', Yii::t('app', 'Способ получения:'), ['class' => ['badge-info']]);
		$output .= Html::tag('p', $this->model->user_condition, ['class' => ['badge-info']]);

		if ($has_badge) {
			$output .= Html::tag('p', Yii::t('app', 'Награда получена!'), ['class' => ['badge-progress']]);
		} else {
			$output .= Html::tag('p', Yii::t('app', 'Уровень прогресса:'), ['class' => ['badge-info']]);
			$text = Yii::t('app', '{user_count} из {badge_count}', [
				'user_count' => $user_count,
				'badge_count' => $badge_count,
			]);
			$output .= Html::tag('p', $text, ['class' => ['badge-progress']]);
		}

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::tag('div', $name, ['class' => ['h6', 'mt-0', 'text-center', 'fw-bold']]);

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

}
