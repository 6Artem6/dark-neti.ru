<?php
namespace app\widgets\alert\register;

use Yii;
use yii\bootstrap5\Widget;


class FlashAlerts extends Widget
{

	public $alertTypes = [
		'error'   => 'text-error',
		'danger'  => 'text-error',
		'success' => 'text-success',
		'info'    => 'text-info',
		'warning' => 'text-secondary'
	];

	public function run()
	{
		$session = Yii::$app->session;
		foreach (array_keys($this->alertTypes) as $type) {
			$flash = $session->getFlash($type);
			$color = $this->alertTypes[$type] ?? $this->alertTypes['info'];
			foreach ((array) $flash as $i => $message) {
				echo Alert::widget([
					'message' => $message,
					'id' => $this->getId() . '-' . $type . '-' . $i,
					'color' => $color,
				]);
			}
			$session->removeFlash($type);
		}
	}
}
