<?php

namespace app\widgets\badge;

use Yii;
use yii\bootstrap5\{Html, Modal, Widget};

use app\models\badge\Badge;


class BadgeModal extends Widget
{

	public Badge $model;

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
		$id = 'badgeForm-' . $this->model->badge_id;
		$title = Yii::t('app', 'Вы получили награду!');
		$name = '«'.$this->model->type->latin_name.'»';

		$badge_count = $this->model->getLevelCount();

		$output = "";
		$output .= Html::beginTag('div', [
			'id' => $id,
			'class' => ['modal', 'fade', 'modal-alert'],
			'tabindex' => -1,
			'aria-hidden' => 'true',
		]);
		$output .= Html::beginTag('div', ['class' => ['modal-dialog', 'modal-dialog-centered'], 'style' => ['max-width' => '300px']]);
		$output .= Html::beginTag('div', ['class' => ['modal-content']]);
		$output .= Html::beginTag('div', ['class' => ['modal-header']]);
		$output .= Html::tag('h5', $title, ['class' => ['modal-title']]);
		$output .= Html::button(null, [
			'class' => ['btn-close'],
			'data-bs-dismiss' => 'modal',
		]);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['modal-body']]);

		$output .= Html::beginTag('div', ['class' => ['badge-div', 'badge-background']]);
		$output .= $this->model->getBadgeHtml();
		$output .= Html::beginTag('div', ['class' => ['rounded-circle']]);
		$output .= Html::beginTag('div', ['class' => ['rounded-circle', 'badge-layer']]);
		$output .= Html::beginTag('div', ['class' => ['badge-text']]);

		$output .= Html::tag('p', $name, ['class' => ['badge-info']]);
		$output .= Html::tag('p', $this->model->type->description, ['class' => ['badge-info']]);
		$output .= Html::tag('p', Yii::t('app', 'Способ получения:'), ['class' => ['badge-info']]);
		$output .= Html::tag('p', $this->model->user_condition, ['class' => ['badge-info']]);
		$text = Yii::t('app', '{user_count} из {badge_count}', [
			'user_count' => $badge_count,
			'badge_count' => $badge_count,
		]);
		$output .= Html::tag('p', $text, ['class' => ['badge-progress']]);
		$output .= Html::tag('p', Yii::t('app', 'Награда получена!'), ['class' => ['badge-progress']]);

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::tag('div', $name, ['class' => ['h6', 'mt-0', 'text-center', 'fw-bold']]);

		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['modal-footer']]);
		$output .= Html::button(Yii::t('app', 'Отлично!'), [
			'class' => ['btn', 'btn-light', 'rounded-pill'],
			'data-bs-dismiss' => 'modal',
		]);
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

}
