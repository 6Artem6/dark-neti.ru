<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Modal, Widget};


class LogoutModalForm extends Widget
{

	public function run()
	{
		$id = 'logoutForm';
		$title = Yii::t('app', 'Ты уверен, что хочешь выйти?');
		$widget = new Modal([
			'options' => ['id' => $id],
			'title' => $title,
			'titleOptions' => ['class' => 'h5'],
			'centerVertical' => true,
			'scrollable' => true,
		]);

		echo Html::beginForm(
			['/site/logout'],
			'POST',
			['id' => 'logout-form', 'class' => ['float-end']]
		);
		echo Html::submitButton(Yii::t('app', 'Выйти'), [
			'class' => ['btn', 'btn-danger', 'rounded-pill', 'me-2'],
		]);
		echo Html::button(Yii::t('app', 'Остаться'), [
			'class' => ['btn', 'btn-light', 'rounded-pill'],
			'data-bs-dismiss' => 'modal',
		]);
		echo Html::endForm();

		return $widget->run();
	}

}
