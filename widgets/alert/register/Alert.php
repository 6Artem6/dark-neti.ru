<?php
namespace app\widgets\alert\register;

use Yii;
use yii\bootstrap5\{Html, Modal, Widget};


class Alert extends Widget
{

	public ?string $id = null;
	public string $color = 'text-secondary';
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
		$title = Yii::t('app', 'Уведомление');
		$widget = new Modal([
			'options' => ['id' => $this->id, 'class' => ['modal-alert']],
			'dialogOptions' => ['class' => ['modal-blur', 'text-justify']],
			'title' => $title,
			'titleOptions' => ['class' => ['h5', $this->color]],
			'bodyOptions' => ['class' => ['mx-2']],
			'footer' => $this->renderFooter(),
			'centerVertical' => true,
			'scrollable' => true,
		]);
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo $this->message;
		echo Html::endTag('p');
		return $widget->run();
	}

	protected function renderFooter()
	{
		return Html::button(Yii::t('app', 'Закрыть'), [
			'class' => ['btn', 'btn-light', 'rounded-pill'],
			'data-bs-dismiss' => 'modal',
		]);
	}

}
