<?php

namespace app\widgets\badge;

use Yii;
use yii\bootstrap5\Widget;

use app\models\badge\UserBadge;


class FlashBadges extends Widget
{
	public function run()
	{
		$session = Yii::$app->session;
		if ($session->getFlash('badge')) {
			$user_id = Yii::$app->user->identity->id;
			$list = UserBadge::getUserBadgesUnseen($user_id);
			foreach ($list as $record) {
				echo BadgeModal::widget([
					'model' => $record->badge,
				]);
			}
			$session->removeFlash('badge');
		}
	}
}
