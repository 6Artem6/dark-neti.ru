<?php

namespace app\widgets\support;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\request\Support;


class SupportText extends Widget
{

	public Support $model;
	public bool $is_request = true;

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
		$is_moderator = Yii::$app->user->identity->isModerator();

		$output = "";

		$output .= Html::beginTag("div", ["class" => ['bg-light', 'px-3', 'py-2', 'rounded', 'position-relative']]);
		$output .= Html::beginTag("div", ["class" => ['d-flex', 'justify-content-between', 'border-bottom', 'mb-2',]]);
		$output .= Html::beginTag("div");
		if ($this->is_request and $is_moderator) {
			$output .= Yii::t('app', 'Пользователь: {name}', ['name' => $this->model->author->name]);
		} elseif ($this->is_request and !$is_moderator) {
			$output .= Yii::t('app', 'Вы: ');
		} elseif (!$this->is_request and $is_moderator) {
			$output .= Yii::t('app', 'Вы: ');
		} elseif (!$this->is_request and !$is_moderator) {
			$output .= Yii::t('app', 'Поддержка: ');
		}
		$output .= Html::endTag('div');

		$output .= Html::beginTag("div");
		$output .= Html::beginTag("small");
		if ($this->is_request) {
			$output .= Html::tag('span',
				$this->model->timeFull, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Обращение дано: {datetime}', ['datetime' => $this->model->timeFull])
			]);
		} else {
			$output .= Html::tag('span',
				$this->model->responseTimeFull, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Ответ дан: {datetime}', ['datetime' => $this->model->responseTimeFull])
			]);
		}
		$output .= Html::endTag('small');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => 'support-text']);
		if ($this->is_request) {
			$output .= Html::tag("div", $this->model->support_text, ["class" => ['mb-0', 'text-end']]);
		} else {
			$output .= Html::tag("div", $this->model->response_text, ["class" => ['mb-0']]);
		}
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

}
