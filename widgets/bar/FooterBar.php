<?php
namespace app\widgets\bar;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\helpers\HtmlHelper;


class FooterBar extends Widget
{

	public function run()
	{
		$output = "";

		$output .= Html::beginTag('footer', ['class' => ["card", "card-body"]]);
		$output .= Html::beginTag('div', ['class' => ["row", "g-4"]]);
		$output .= Html::beginTag('div', ['class' => ["col-md-8"]]);
		$output .= Html::beginTag('ul', ['class' => ["nav", "lh-1"]]);
		$output .= Html::beginTag('li', ['class' => ["nav-item"]]);
		$output .= Html::a(Yii::t('app', 'Кодекс чести'), '#', [
			'class' => ['nav-link', 'ps-0'],
			'data-bs-toggle' => "modal",
			'data-bs-target' => "#honorCodeModal"
		]);
		$output .= Html::endTag('li');
		$output .= Html::beginTag('li', ['class' => ["nav-item"]]);
		$output .= Html::a(Yii::t('app', 'Правила'), '#', [
			'class' => ['nav-link', 'ps-0'],
			'data-bs-toggle' => "modal",
			'data-bs-target' => "#rulesModal"
		]);
		$output .= Html::endTag('li');
		$output .= Html::beginTag('li', ['class' => ["nav-item"]]);
		if (Yii::$app->user->identity->isModerator()) {
			$output .= Html::a(Yii::t('app', 'Поддержка'),
				['/moderator/report'],
				['class' => ['nav-link', 'ps-0']]
			);
		} else {
			$output .= Html::a(Yii::t('app', 'Поддержка'),
				['/user/support'],
				['class' => ['nav-link', 'ps-0']]
			);
		}
		$output .= Html::endTag('li');
		$output .= Html::endTag('ul');
		$output .= Html::tag('p',
			HtmlHelper::getFooterText(),
			['class' => ["mb-0", "mt-4"]]
		);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('footer');

		return $output;
	}

}
