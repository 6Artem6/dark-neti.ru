<?php
namespace app\widgets\bar;

use Yii;
use yii\helpers\Url;
use yii\bootstrap5\{Html, Widget};

use app\models\user\User;


class LeftBar extends Widget
{

	protected User $user;

	public function run()
	{
		$this->user = Yii::$app->user->identity;
		$user_data = $this->user->data;
		$link = $this->getLink();

		$output = '';

		$output .= Html::beginTag('div', ['class' => ['navbar', 'navbar-vertical', 'navbar-light']]);
		$output .= Html::beginTag('div', [
			'id' => 'left-bar',
			'class' => ['offcanvas', 'offcanvas-lg', 'offcanvas-start', 'p-2'],
			'data-bs-scroll' => 'true',
			'tabindex' => -1,
		]);
		$output .= Html::beginTag('div', ['class' => ['d-flex', 'align-items-center', 'offcanvas-logo']]);
		$output .= Html::a(
			Html::img('/logo/logo.svg', ['class' => ['logo-text', 'light-mode-item']]) .
			Html::img('/logo/logo-dark.svg', ['class' => ['logo-text', 'dark-mode-item']]),
			['/'],
			['class' => ['w-100', 'px-2']]
		);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['sm-divider', 'd-flex', 'align-items-center', 'py-3']]);
		$output .= Html::tag('hr', null, ['class' => ['my-0', 'w-100']]);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['d-flex', 'align-items-center', 'px-4']]);
		$output .= Html::beginTag('div', ['class' => ['avatar', 'avatar-lg']]);
		$output .= Html::a(
			Html::img($user_data->getAvatarLink(), [
				'class' => ['avatar-img', 'rounded-circle']
			]),
			$this->user->getPageLink()
		);
		$output .= Html::endTag('div');
		$output .= Html::a($this->user->shortname,
			$this->user->getPageLink(),
			['class' => ['h5', 'ms-3', 'me-auto', 'mb-0']]
		);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['d-flex', 'align-items-center', 'py-3']]);
		$output .= Html::tag('hr', null, ['class' => ['my-0', 'w-100']]);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', [
			'class' => ['offcanvas-body', 'scroll-body', 'pt-0', 'px-2'],
			'style' => ['overflow-y' => 'hidden']
		]);

		$output .= Html::ul($this->getTabs(), [
			'class' => ['nav', 'nav-link-secondary', 'flex-column', 'fw-bold', 'gap-2'],
			'item' => function($item, $index) use($link) {
				$button_classes = ['nav-link', 'py-1'];
				$icon_classes = ['fa', 'fa_icon', $item['class']];
				$text = Html::tag('span', null, ['class' => $icon_classes]) . $item['text'];
				if (!empty($item['link'])) {
					if ($item['link'] == $link) {
						$button_classes[] = 'active';
					}
					$button = Html::a($text, $item['link'], ['class' => $button_classes]);
				} else {
					$button = Html::tag('span', $text, ['class' => $button_classes]);
				}
				$item['options']['class'] = ['nav-item'];
				return Html::tag('li', $button, $item['options']);
			},
		]);
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		return $output;
	}

	protected function getTabs()
	{
		$anchor = '#tabs';
		$tabs = [
			[
				'text' => Yii::t('app', 'Моя лента'),
				'link' => Url::to(['/feed']),
				'class' => 'fa-house',
				'options' => [
					'id' => 'nav-feed',
				]
			],
			[
				'text' => Yii::t('app', 'Вопросы и ответы'),
				'link' => Url::to(['/question']),
				'class' => 'fa-circle-question',
				'options' => [
					'id' => 'nav-question',
				]
			],
			[
				'text' => Yii::t('app', 'Мои подписки'),
				'link' => $this->user->getPageLink(tab: 'follow-questions') . $anchor,
				'class' => 'fa-bell',
				'options' => [
					'id' => 'nav-follow-questions',
				]
			],
			[
				'text' => Yii::t('app', 'Предметы'),
				'link' => Url::to(['/discipline']),
				'class' => 'fa-book-open',
				'options' => [
					'id' => 'nav-disciplines',
				]
			],
			[
				'text' => Yii::t('app', 'Пользователи'),
				'link' => Url::to(['/user']),
				'class' => 'fa-users',
				'options' => [
					'id' => 'nav-users',
				]
			],
			[
				'text' => Yii::t('app', 'Мои достижения'),
				'link' => $this->user->getPageLink(tab: 'badges') . $anchor,
				'class' => 'fa-medal',
				'options' => [
					'id' => 'nav-badges',
				]
			],
			/*[
				'text' => Yii::t('app', 'Обращения'),
				'link' => Url::to(['/report']),
				'class' => 'fa-lightbulb',
				'options' => [
				'id' => 'nav-report',
			]
			],*/

		];
		if ($this->user->isModerator()) {
			$tabs[] = [
				'text' => Yii::t('app', 'Поддержка'),
				'link' => Url::to(['/moderator/report']),
				'class' => 'fa-square-check',
				'options' => [
					'id' => 'nav-moderator-report',
				]
			];
			$tabs[] = [
				'text' => Yii::t('app', 'Теги'),
				'link' => Url::to(['/moderator/tags']),
				'class' => 'fa-tags',
				'options' => [
					'id' => 'nav-moderator-tags',
				]
			];
		} else {
			$tabs[] = [
				'text' => Yii::t('app', 'Поддержка'),
				'link' => Url::to(['/user/support']),
				'class' => 'fa-square-check',
				'options' => [
					'id' => 'nav-report',
				]
			];
		}
		$tabs[] = [
			'text' => Yii::t('app', 'Настройки'),
			'link' => Url::to(['/user/settings']),
			'class' => 'fa-gear',
			'options' => [
				'id' => 'nav-settings',
			]
		];
		$tabs[] = [
			'text' => Yii::t('app', 'Кодекс чести'),
			'class' => 'fa-scroll',
			'options' => [
				'id' => 'nav-honor-code',
				'style' => ['cursor' => 'pointer'],
				'data' => [
					'bs-toggle' => 'modal',
					'bs-target' => '#honorCodeModal'
				]
			]
		];
		$tabs[] = [
			'text' => Yii::t('app', 'Правила'),
			'class' => 'fa-book',
			'options' => [
				'id' => 'nav-rules',
				'style' => ['cursor' => 'pointer'],
				'data' => [
					'bs-toggle' => 'modal',
					'bs-target' => '#rulesModal'
				]
			]
		];
		return $tabs;
	}

	protected function getLink()
	{
		$controller = Yii::$app->controller;
		if ($controller->action->id == 'index') {
			$link = $controller->id;
		} else {
			$link = $controller->route;
		}
		return '/' . $link;
	}

}
