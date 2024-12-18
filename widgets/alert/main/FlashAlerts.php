<?php
namespace app\widgets\alert\main;

use Yii;
use yii\bootstrap5\Widget;


class FlashAlerts extends Widget
{

	public $alertTypes = [
		'error'   => 'flash-error',
		'danger'  => 'flash-error',
		'success' => 'flash-success',
		'info'    => 'flash-info',
		'warning' => 'flash-warning'
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
