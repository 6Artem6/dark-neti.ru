<?php
namespace app\widgets\bar;

use Yii;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\Url;

use app\models\user\User;
use app\models\helpers\{ModelHelper, HtmlHelper};


class NavBar extends Widget
{

	protected ?User $user = null;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		$this->user = Yii::$app->user->identity;
		return true;
	}

	public function run()
	{
		$output = '';

		$output .= Html::beginTag('header', ['class' => 'navbar-light fixed-top header-static bg-mode']);
		$output .= Html::beginTag('nav', ['class' => 'navbar navbar-expand-lg']);
		$output .= Html::beginTag('div', ['class' => ['container-fluid', 'header-container', 'px-lg-4', 'px-xl-4', 'mx-auto']]);

		$output .= Html::beginTag('a', ['href' => Url::to('/')]);
		$output .= Html::img('/logo/logo.svg', ['class' => ['logo-lg', 'light-mode-item', 'me-auto']]);
		$output .= Html::img('/logo/logo-dark.svg', ['class' => ['logo-lg', 'dark-mode-item', 'me-auto']]);
		$output .= Html::endTag('a');

		$output .= Html::img('/logo/logo.svg', [
			'class' => ['logo-md', 'light-mode-item', 'me-auto'],
			'type' => 'button',
			'aria-controls' => 'left-bar',
			'data' => [
				'bs-toggle' => 'offcanvas',
				'bs-target' => '#left-bar',
			],
		]);
		$output .= Html::img('/logo/logo-dark.svg', [
			'class' => ['logo-md', 'dark-mode-item', 'me-auto'],
			'type' => 'button',
			'aria-controls' => 'left-bar',
			'data' => [
				'bs-toggle' => 'offcanvas',
				'bs-target' => '#left-bar',
			],
		]);

		$output .= Html::img('/logo/logo-sm.svg', [
			'class' => ['logo-sm', 'light-mode-item', 'me-auto'],
			'type' => 'button',
			'aria-controls' => 'left-bar',
			'data' => [
				'bs-toggle' => 'offcanvas',
				'bs-target' => '#left-bar',
			],
		]);
		$output .= Html::img('/logo/logo-sm-dark.svg', [
			'class' => ['logo-sm', 'dark-mode-item', 'me-auto'],
			'type' => 'button',
			'aria-controls' => 'left-bar',
			'data' => [
				'bs-toggle' => 'offcanvas',
				'bs-target' => '#left-bar',
			],
		]);

		$output .= Html::beginTag('div', ['id' => 'navbarCollapse', 'class' => ['collapse', 'navbar-collapse', 'ms-lg-3']]);

		$output .= Html::beginTag('div', ['class' => ['nav', 'align-items-center', 'flex-nowrap', 'd-flex', 'mt-3', 'mt-lg-0', 'px-0']]);
		$output .= Html::beginTag('div', ['class' => 'nav-item p-2 w-100']);
		$output .= Html::beginForm(Url::to(['/question']), 'GET', ['id' => 'search-form', 'class' => ['rounded', 'position-relative']]);
		$output .= Html::input('search', ModelHelper::getSearchParamName('text'), null, [
			'id' => 'navbar-search-field',
			'class' => 'form-control bg-light pe-0',
			'aria-label' => Yii::t('app', 'Поиск'),
			'placeholder' => Yii::t('app', 'Введите текст для поиска')
		]);
		$output .= Html::submitButton(
			Html::tag('i', null, ['class' => 'bi bi-search fs-6']),
			['class' => ['btn', 'bg-transparent', 'position-absolute', 'top-50', 'end-0', 'translate-middle-y', 'px-2', 'py-0']]
		);
		$output .= Html::endForm();
		$output .= Html::beginTag('div', [
			'id' => 'search-list',
			'class' => ['list-group', 'dropdown-menu', 'dropdown-menu-end', 'd-none'],
			'aria-labelledby' => 'navbar-search-field',
		]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['id' => 'nav-search-close-block', 'class' => ['p-2', 'flex-shrink-1']]);
		$close_icon = Html::tag('span', null, ['id' => 'nav-btn-search-close', 'class' => ['bi', 'bi-x', 'fs-5'] ]);
		$output .=  Html::button($close_icon, [
			'class' => ['btn', 'btn-light', 'rounded-circle', 'icon-lg'],
			'type' => 'button',
			'aria-expanded' => 'false',
			'aria-controls' => 'navbarCollapse',
			'data' => [
				'bs-toggle' => 'collapse',
				'bs-target' => '#navbarCollapse',
			],
		]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		if (!is_null($this->user)) {
			$output .= $this->renderRightNavBar();
		}
		$output .= Html::endTag('div');
		$output .= Html::endTag('header');
		$output .= Html::endTag('nav');

		return $output;
	}

	protected function renderRightNavBar()
	{
		$output = '';

		$output .= Html::beginTag('ul', ['class' => ['nav', 'flex-nowrap', 'align-items-center', 'list-unstyled', 'nav-buttons', 'ms-sm-2']]);
		$output .= Html::beginTag('li', ['class' => ['nav-item', 'dropdown', 'me-2']]);
		$output .= Html::a(Yii::t('app', 'Задать вопрос'),
			Url::to(['/question/create']),
			['id' => 'question-create', 'class' => ['btn', 'btn-md', 'btn-success', 'text-white', 'rounded-pill', 'fw-bolder', 'px-3', 'w-100']]
		);
		$output .= Html::endTag('li');

		$search_icon = Html::tag('span', null, ['class' => ['bi', 'bi-search', 'navbar-btn-search', 'fs-5']]);
		$output .= Html::beginTag('li', ['class' => ['nav-item', 'dropdown', 'navbar-block-sm', 'me-2']]);
		$output .=  Html::button($search_icon, [
			'class' => ['navbar-toggler', 'icon-lg', 'btn', 'btn-light', 'rounded-circle'],
			'type' => 'button',
			'aria-expanded' => 'false',
			'aria-controls' => 'navbarCollapse',
			'data' => [
				'bs-toggle' => 'collapse',
				'bs-target' => '#navbarCollapse',
			],
		]);
		$output .= Html::endTag('li');

		$output .= Html::beginTag('li', ['class' => ['nav-item', 'dropdown', 'me-2']]);
		$notif_icon = Html::tag('span', null, [
			'id' => 'notifCircle',
			'class' => ['badge-notif', 'animation-blink'],
			'style' => ['display' => 'none']
		]);
		$notif_icon .= Html::tag('i', null, ['class' => 'bi bi-bell-fill fs-5']);
		$output .= Html::button($notif_icon, [
			'id' => 'notifDropdown',
			'class' => 'nav-link icon-lg btn btn-light rounded-circle',
			'role' => 'button',
			'aria-expanded' => 'false',
			'data' => [
				'bs-toggle' => 'dropdown',
				'bs-auto-close' => 'outside',
			],
		]);
		$output .= Html::beginTag('div', [
			'id' => 'notifDiv',
			'class' => ['dropdown-menu', 'dropdown-animation', 'dropdown-menu-end', 'dropdown-menu-size-md', 'p-0', 'shadow-lg', 'border-0'],
			'aria-labelledby' => 'notifDropdown'
		]);
		$output .= Html::beginTag('div', ['class' => ['card']]);
		$output .= Html::beginTag('div', ['class' => ['card-header', 'd-flex', 'justify-content-between', 'align-items-center']]);
		$output .= Html::beginTag('h6', ['class' => ['m-0']]);
		$output .= Html::a(Yii::t('app', 'Уведомления'),
			Url::to(['/user/notification']),
			['class' => ['text-secondary', 'link-primary']]
		);
		$output .= Html::tag('span',
			Yii::t('app', 'Новых: {count}', ['count' => HtmlHelper::getCountText(0)]),
			[
				'id' => 'notif-count',
				'data-count' => 0,
				'class' => ['badge', 'bg-danger', 'bg-opacity-10', 'text-danger ms-2'],
				'style' => ['display' => 'none'],
			]
		);
		$output .= Html::endTag('h6');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', [
			'class' => ['card-body', 'scroll-body', 'p-0'],
			'style' => ['max-height' => '300px', 'overflow-y' => 'hidden']
		]);
		$output .= Html::tag('div', null, ['id' => 'messages-last', 'class' => ['p-2']]);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['card-footer', 'text-center']]);
		$output .= Html::a(Yii::t('app', 'Просмотреть все уведомления'),
			Url::to(['/user/notification']),
			['class' => ['btn', 'btn-sm', 'btn-outline-primary']]
		);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('li');

		$output .= Html::beginTag('li', ['class' => 'nav-item dropdown me-0']);
		$avatar = Html::img($this->user->data->getAvatarLink(), ['class' => ['avatar-img', 'rounded-circle']]);
		$output .= Html::button($avatar, [
			'id' => 'profileDropdown',
			'class' => ['nav-link', 'btn', 'rounded-circle', 'icon-lg', 'p-0'],
			'role' => 'button',
			'aria-expanded' => 'false',
			'data' => [
				'bs-toggle' => 'dropdown',
				'bs-auto-close' => 'outside',
				'bs-display' => 'static',
			],
		]);
		$output .= Html::beginTag('ul', [
			'id' => 'dropdown-nav',
			'class' => ['dropdown-menu', 'dropdown-animation', 'dropdown-menu-end', 'small', 'me-md-n3'],
			'aria-labelledby' => 'profileDropdown',
		]);
		$output .= Html::beginTag('li');
		$output .= Html::beginTag('div', ['class' => ['d-flex', 'align-items-center', 'position-relative']]);
		$output .= Html::beginTag('div', ['class' => ['avatar', 'mx-3']]);
		$output .= Html::a(
			Html::img($this->user->data->getAvatarLink(), ['class' => ['avatar-img', 'rounded-circle']]),
			$this->user->getPageLink(),
			['class' => ['h6', 'stretched-link']]
		);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div');
		$output .=  Html::a($this->user->name,
			$this->user->getPageLink(),
			['class' => ['h6', 'stretched-link']]
		);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('li');

		$output .= Html::tag('li', null, ['class' => ['dropdown-divider', 'my-1', 'navbar-block-xs']]);

		$output .= Html::beginTag('li');
		$output .= Html::button(Yii::t('app', 'Поиск'), [
			'id' => 'dropdown-search',
			'class' => ['dropdown-item', 'bi', 'bi-search', 'navbar-block-xs', 'fa-fw'],
			'type' => 'button',
			'aria-expanded' => 'false',
			'aria-controls' => 'navbarCollapse',
			'data' => [
				'bs-toggle' => 'collapse',
				'bs-target' => '#navbarCollapse',
			],
		]);
		$output .= Html::endTag('li');

		$output .= Html::tag('li', null, ['class' => ['dropdown-divider', 'my-1']]);

		$output .= Html::beginTag('li');
		$output .= Html::a(Yii::t('app', 'Настройки уведомлений'),
			Url::to(['/user/settings']),
			['class' => ['dropdown-item', 'bi', 'bi-gear', 'fa-fw']]
		);
		$output .= Html::endTag('li');

		$output .= Html::tag('li', null, ['class' => ['dropdown-divider', 'my-1']]);

		$output .= Html::beginTag('li');
		$output .= Html::a(Yii::t('app', 'Поддержка'),
			Url::to(['/site/support']),
			['class' => ['dropdown-item', 'bi', 'bi-life-preserver', 'fa-fw']]
		);
		$output .= Html::endTag('li');

		$output .= Html::tag('li', null, ['class' => ['dropdown-divider', 'my-1']]);

		$output .= Html::beginTag('li');
		$output .= Html::a(Yii::t('app', 'Путеводитель по сайту'),
			Url::to(['/feed', 'tour' => true]),
			['class' => ['dropdown-item', 'bi', 'bi-compass', 'fa-fw']]
		);
		$output .= Html::endTag('li');

		$output .= Html::tag('li', null, ['class' => ['dropdown-divider', 'my-1']]);

		$output .= Html::beginTag('li');
		$title = Html::beginTag('div', ['class' => 'modeswitch-item']);
		$title .= Html::tag('div', null, ['class' => 'modeswitch-icon']);
		$title .= Html::endTag('div');
		$title .= Html::tag('span', Yii::t('app', 'Тёмная тема'));
		$output .= HtmlHelper::actionButton($title, 'switch-theme', null, [
			'tag' => 'a',
			'id' => 'darkModeSwitch',
			'class' => ['modeswitch-wrap', 'text-secondary', 'my-2'],
		]);
		$output .= Html::endTag('li');

		$output .= Html::tag('li', null, ['class' => ['dropdown-divider', 'my-1']]);

		$output .= Html::beginTag('li');
		$title = Html::tag('span', Yii::t('app', 'Выйти'), ['class' => ['bi', 'bi-power', 'fa-fw']]);
		$output .= Html::button($title, [
			'class' => ['dropdown-item'],
			'data' => [
				'bs-toggle' => 'modal',
				'bs-target' => '#logoutForm',
			],
		]);
		$output .= Html::endTag('li');

		$output .= Html::endTag('ul');

		$output .= Html::endTag('li');

		$output .= Html::endTag('ul');

		return $output;

	}

}
