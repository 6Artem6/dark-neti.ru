<?php
namespace app\widgets\alert\main;

use Yii;
use yii\bootstrap5\{Html, Widget};


class Alert extends Widget
{

	public ?string $id = null;
	public string $color = 'flash-white';
	public ?string $message = null;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if (empty($this->id)) {
			$this->id = $this->getId();
		}
		return true;
	}

	public function run()
	{
		$output = "";
		$output .= Html::beginTag('div', [
			'id' => $this->id,
			'class' => ["toast", "fade", "hide"],
			'role' => "alert",
			"aria-live" => "assertive",
			"aria-atomic" => "true",
			'data-bs-delay' => 5000
		]);
		$output .= Html::beginTag('div', ['class' => "toast-header"]);
		$output .= Html::beginTag('svg', [
			'class' => ["bd-placeholder-img", "rounded", "me-2"],
			'width' => 20,
			"height" => 20,
			'xmlns' => "http://www.w3.org/2000/svg",
			'aria-hidden' => "true",
			'preserveAspectRatio' => "xMidYMid slice",
			'focusable' => "false",
		]);
		$output .= Html::tag('rect', null, [
			'class' => $this->color,
			'width' => "100%",
			'height' => "100%",
		]);
		$output .= Html::endTag('svg');
		$output .= Html::tag('strong', Yii::t('app', 'Уведомление'), ['class' => 'me-auto']);
		$output .= Html::tag('small', Yii::t('app', 'Только что'));
		$output .= Html::button(null, [
			'type' => "button",
			'class' => "btn-close",
			'data-bs-dismiss' => "toast",
			'aria-label' => Yii::t('app', 'Скрыть'),
		]);
		$output .= Html::endTag('div');
		$output .= Html::tag('div', $this->message, ['class' => "toast-body"]);
		$output .= Html::endTag('div');
		return $output;
	}

}
