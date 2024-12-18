<?php

namespace app\widgets\user;

use Yii;
use yii\bootstrap5\{Accordion, Html, LinkPager, Widget};
use yii\data\Pagination;
use yii\helpers\Url;

use app\models\request\ReportType;
use app\models\badge\{Badge, UserBadge};
use app\models\user\User;
use app\models\data\RecordList;
use app\models\helpers\HtmlHelper;

use app\widgets\badge\BadgeRecord;
use app\widgets\discipline\DisciplineRecord;
use app\widgets\question\FeedRecord;

use app\assets\actions\UserViewAsset;


class ProfileRecord extends Widget
{

	public User $model;
	public string $tab;
	protected array $list;
	protected ?Pagination $pages = null;

	protected const TAB_ABOUT = 'about';
	protected const TAB_QUESTIONS = 'questions';
	protected const TAB_ANSWERS = 'answers';
	protected const TAB_COMMENTS = 'comments';
	protected const TAB_LIKE = 'like';

	protected const TAB_FOLLOW_QUESTIONS = 'follow-questions';
	protected const TAB_FOLLOW_DISCIPLINES = 'follow-disciplines';
	protected const TAB_FOLLOW_USERS = 'follow-users';

	protected const TAB_BADGES = 'badges';
	protected const TAB_BADGES_BRONZE = 'badges-bronze';
	protected const TAB_BADGES_SILVER = 'badges-silver';
	protected const TAB_BADGES_GOLD = 'badges-gold';
	protected const TAB_BADGES_PLATINUM = 'badges-platinum';


	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if (!in_array($this->tab, $this->getProfileSectionNames())) {
			$this->tab = static::TAB_ABOUT;
		}
		UserViewAsset::register($this->view);
		return true;
	}

	public function run()
	{
		if ($this->model->data->isSelf) {
			$title = Yii::t('app', 'Моя страница');
		} else {
			$title = Yii::t('app', 'Страница Пользователя {name}', [
				'name' => $this->model->name
			]);
		}
		$this->view->title = $title;

		$this->loadList();
		$output = '';
		$output .= $this->getHeader();
		$output .= $this->getUserInfo();
		$output .= $this->getBody();
		return $output;
	}

	protected function getProfileSectionNames(): array
	{
		return array_merge(
			array_keys($this->getProfileTabs()),
			array_keys($this->getFollowTabs()),
			array_keys($this->getBadgeTabs())
		);
	}

	protected function getProfileTabs(): array
	{
		$anchor = '#tabs';
		return [
			static::TAB_ABOUT => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Профиль')),
				'link' => $this->model->getPageLink() . $anchor,
				'class' => 'bi-person'
			],
			static::TAB_QUESTIONS => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Вопросы')),
				'link' => $this->model->getPageLink(tab: static::TAB_QUESTIONS) . $anchor,
				'class' => 'bi-question-circle'
			],
			static::TAB_ANSWERS => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Ответы')),
				'link' => $this->model->getPageLink(tab: static::TAB_ANSWERS) . $anchor,
				'class' => 'bi-chat-left-text'
			],
			static::TAB_COMMENTS => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Комментарии')),
				'link' => $this->model->getPageLink(tab: static::TAB_COMMENTS) . $anchor,
				'class' => 'bi-chat'
			],
			static::TAB_FOLLOW_QUESTIONS => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Подписки')),
				'link' => $this->model->getPageLink(tab: static::TAB_FOLLOW_QUESTIONS) . $anchor,
				'class' => 'bi-bell'
			],
			static::TAB_LIKE => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Нравится')),
				'link' => $this->model->getPageLink(tab: static::TAB_LIKE) . $anchor,
				'class' => 'bi-hand-thumbs-up'
			],
			static::TAB_BADGES => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Достижения')),
				'link' => $this->model->getPageLink(tab: static::TAB_BADGES) . $anchor,
				'class' => 'bi-award'
			],
		];
	}

	protected function getFollowTabs(): array
	{
		$anchor = '#tabs';
		return [
			static::TAB_FOLLOW_QUESTIONS => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Вопросы')),
				'link' => $this->model->getPageLink(tab: static::TAB_FOLLOW_QUESTIONS) . $anchor,
				'class' => 'bi-question-circle'
			],
			static::TAB_FOLLOW_DISCIPLINES => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Предметы')),
				'link' => $this->model->getPageLink(tab: static::TAB_FOLLOW_DISCIPLINES) . $anchor,
				'class' => 'bi-book'
			],
			static::TAB_FOLLOW_USERS => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Пользователи')),
				'link' => $this->model->getPageLink(tab: static::TAB_FOLLOW_USERS) . $anchor,
				'class' => 'bi-people'
			],
		];
	}

	protected function getBadgeTabs(): array
	{
		$anchor = '#tabs';
		return [
			static::TAB_BADGES => [
				'text' => Yii::t('app', 'Все'),
				'link' => $this->model->getPageLink(tab: static::TAB_BADGES) . $anchor,
			],
			static::TAB_BADGES_BRONZE => [
				'text' => Yii::t('app', 'Бронза'),
				'link' => $this->model->getPageLink(tab: static::TAB_BADGES_BRONZE) . $anchor,
			],
			static::TAB_BADGES_SILVER => [
				'text' => Yii::t('app', 'Серебро'),
				'link' => $this->model->getPageLink(tab: static::TAB_BADGES_SILVER) . $anchor,
			],
			static::TAB_BADGES_GOLD => [
				'text' => Yii::t('app', 'Золото'),
				'link' => $this->model->getPageLink(tab: static::TAB_BADGES_GOLD) . $anchor,
			],
			static::TAB_BADGES_PLATINUM => [
				'text' => Yii::t('app', 'Платина'),
				'link' => $this->model->getPageLink(tab: static::TAB_BADGES_PLATINUM) . $anchor,
			],
		];
	}

	protected function loadList()
	{
		if ($this->tab == static::TAB_ABOUT) {
			$this->list = [
				'disciplines' => RecordList::getDisciplineAuthorBestList($this->model->id, 6),
				'questions' => RecordList::getQuestionAuthorBestListAbout($this->model->id, 5),
				'answers' => RecordList::getAnswerAuthorBestListAbout($this->model->id, 5),
				'badges' => [
					'user_badges' => UserBadge::getUserBadgesImportant($this->model->id),
					'user_badge_data' => $this->model->data->badgeData,
				]
			];
		} elseif ($this->tab == static::TAB_QUESTIONS) {
			list($this->list, $this->pages) = RecordList::getQuestionAuthorBestList($this->model->id);
		} elseif ($this->tab == static::TAB_ANSWERS) {
			list($this->list, $this->pages) = RecordList::getAnswerAuthorBestList($this->model->id);
		} elseif ($this->tab == static::TAB_COMMENTS) {
			list($this->list, $this->pages) = RecordList::getCommentAuthorBestList($this->model->id);
		} elseif ($this->tab == static::TAB_FOLLOW_QUESTIONS) {
			list($this->list, $this->pages) = RecordList::getQuestionAuthorFollowList($this->model->id);
		} elseif ($this->tab == static::TAB_FOLLOW_DISCIPLINES) {
			$this->list = RecordList::getDisciplineFollowedList($this->model->id);
		} elseif ($this->tab == static::TAB_FOLLOW_USERS) {
			$this->list = RecordList::getUserFollowedList($this->model->id);
		} elseif ($this->tab == static::TAB_LIKE) {
			$this->list = RecordList::getAuthorLikeList($this->model->id);
		} elseif (in_array($this->tab, array_keys($this->getBadgeTabs()))) {
			$this->list = [
				'badges' => Badge::getListByLevel(),
				'user_badges' => UserBadge::getUserBadges($this->model->id),
				'user_badge_data' => $this->model->data->badgeData,
			];
		}
	}

	protected function getList()
	{
		$list = $this->getProfileList();
		$this->pages = $this->getPagination($list);
		return array_slice($list, $this->pages->offset, $this->pages->limit);
	}

	protected function getHeader()
	{
		$user_data = $this->model->data;

		$output = '';
		$output .= Html::beginTag('div', ['class' => ['col-sm-12', 'mb-2']]);

		$output .= Html::beginTag('div', ['class' => ['card', 'py-3']]);
		$output .= Html::tag('div', null, ['class' => ['rounded-top']]);
		$output .= Html::beginTag('div', ['class' => ['card-body', 'py-0']]);
		$output .= Html::beginTag('div', ['class' => ['d-sm-flex', 'align-items-center', 'text-center', 'text-sm-start']]);
		$output .= Html::beginTag('div');
		$output .= Html::beginTag('div', ['id' => 'avatar', 'class' => ['avatar', 'avatar-xxl', 'mb-3']]);
		$output .= Html::img($user_data->getAvatarLink(), ['class' => ['avatar-img', 'rounded', 'avatar-image']]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['ms-sm-4', 'mb-0', 'h5', 'profile-name']]);
		$output .= $this->model->name;
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div');
		$output .= Html::beginTag('ul', ['class' => ['list-inline', 'mb-0', 'text-center', 'text-sm-start', 'mt-3', 'mt-sm-0']]);
		$output .= Html::beginTag('li', ['class' => ['list-inline-item', 'me-4']]);
		$output .= $user_data->getOnlineSatus();
		$output .= Html::endTag('li');
		$output .= Html::beginTag('li', ['class' => ['list-inline-item']]);
		$output .= Html::beginTag('span', ['class' => ['bi', 'bi-calendar2-plus', 'bi_icon']]);
		$output .= Yii::t('app', 'Присоединился: {date}', [
			'date' => date('d.m.Y', strtotime($this->model->register->register_datetime))
		]);
		$output .= Html::endTag('span');
		$output .= Html::endTag('li');
		$output .= Html::endTag('ul');
		$output .= Html::endTag('div');

		$link = $this->model->getPageLink(true);
		$output .= HtmlHelper::getShareDiv('share-profile', $link, Yii::t('app', 'Ссылка на профиль'));
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');

		return $output;
	}

	protected function getUserInfo()
	{
		$user_data = $this->model->data;
		$badge_data = $user_data->badgeData;

		$output = '';
		$output .= Html::beginTag('div', ['class' => ['col-sm-12', 'col-lg-3', 'gap-2']]);

		$output .= Html::beginTag('div', ['class' => ['card']]);
		$output .= Html::beginTag('div', ['class' => ['card-body', 'text-center']]);
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::tag('h6', Yii::t('app', 'Подписчиков'), ['class' => ['mb-0']]);
		$output .= Html::tag('h3', $user_data->followers, ['class' => ['mb-0', 'text-info', 'fw-bold']]);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::tag('hr', null, ['class' => ['my-1']]);
		$output .= Html::tag('h6', Yii::t('app', 'Задал вопросов'), ['class' => ['mb-0']]);
		$output .= Html::tag('h3', $badge_data->question_count, ['class' => ['mb-0', 'text-warning', 'fw-bold']]);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::tag('hr', null, ['class' => ['my-1']]);
		$output .= Html::tag('h6', Yii::t('app', 'Дал ответов'), ['class' => ['mb-0']]);
		$output .= Html::tag('h3', $badge_data->answer_count, ['class' => ['mb-0', 'text-primary', 'fw-bold']]);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::tag('hr', null, ['class' => ['my-1']]);
		$output .= Html::tag('h6', Yii::t('app', 'Помогло ответов'), ['class' => ['mb-0']]);
		$output .= Html::tag('h3', $badge_data->answer_helped_count, ['class' => ['mb-0', 'text-success', 'fw-bold']]);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::tag('hr', null, ['class' => ['my-1']]);
		$output .= Html::tag('h6', Yii::t('app', 'Рейтинг'), ['class' => ['mb-0']]);
		$output .= Html::tag('h3', $user_data->rate_sum, ['class' => ['mb-0', 'text-danger', 'fw-bold']]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['id' => 'profile-buttons', 'class' => ['card', 'my-2']]);
		$output .= Html::beginTag('div', ['class' => ['card-body']]);
		$output .= Html::beginTag('div', ['class' => ['w-100', 'mb-3']]);
		if ($this->model->data->isSelf) {
			$title = HtmlHelper::getIconText(Yii::t('app', 'Настройки'), true);
			$output .= Html::a($title, Url::to(['/user/settings']), [
				'class' => [
					'btn', 'btn-outline-light', 'text-secondary',
					'w-100', 'bi', 'bi-gear', 'bi_icon', 'me-2'
				]
			]);
		} elseif ($user_data->isFollowed) {
			$title = HtmlHelper::getIconText(Yii::t('app', 'Вы подписаны'), true);
			$output .= HtmlHelper::actionButton($title, 'unfollow-user', $this->model->id, [
				'class' => [
					'btn', 'btn-light', 'text-secondary',
					'w-100', 'bi', 'bi-bell-fill', 'bi_icon', 'me-2'
				],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Отписаться от пользователя')
			]);
		} else {
			$title = HtmlHelper::getIconText(Yii::t('app', 'Подписаться'), true);
			$output .= HtmlHelper::actionButton($title, 'follow-user', $this->model->id, [
				'class' => [
					'btn', 'btn-outline-light', 'text-secondary',
					'w-100', 'bi', 'bi-bell', 'bi_icon', 'me-2'
				],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Подписаться на пользователя')
			]);
		}
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::button(Yii::t('app', 'Поделиться'), [
			'id' => 'share-profile',
			'class' => [
				'btn', 'btn-outline-light', 'text-secondary', 'btn-share-link',
				'w-100', 'bi', 'bi-share-fill', 'bi_icon'
			],
		]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');

		return $output;
	}

	protected function getBody()
	{
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['col-sm-12', 'col-lg-9', 'vstack', 'gap-2']]);

		$output .= Html::beginTag('div', ['class' => ['card']]);
		$output .= Html::beginTag('div', ['class' => ['card-body', 'py-1', 'px-3']]);
		$output .= Html::tag('a', null, ['id' => 'tabs']);
		$output .= Html::ul($this->getProfileTabs(), [
			'id' => 'user-tabs',
			'class' => [
				'nav', 'nav-bottom-line', 'align-items-center',
				'd-flex', 'justify-content-between', 'mb-0', 'border-0'
			],
			'item' => function ($item, $index) {
				$is_active = false;
				if (($index == static::TAB_FOLLOW_QUESTIONS) and
					in_array($this->tab, array_keys($this->getFollowTabs()))) {
					$is_active = true;
				} elseif (($index == static::TAB_BADGES) and
					in_array($this->tab, array_keys($this->getBadgeTabs()))) {
					$is_active = true;
				} elseif ($index == $this->tab) {
					$is_active = true;
				}
				$classes = ['nav-link', 'bi', 'bi_icon', 'px-1'];
				if ($is_active) {
					$classes[] = 'active';
					$classes[] = $item['class'].'-fill';
				} else {
					$classes[] = $item['class'];
				}
				return Html::tag('li',
					Html::a($item['text'], $item['link'], ['class' => $classes]),
					['class' => ['nav-item']]
				);
			},
		]);
		$output .= Html::endTag('div');
		if (in_array($this->tab, array_keys($this->getFollowTabs()))) {
			$output .= Html::beginTag('div', ['class' => ['card-footer', 'py-2']]);
			$output .= $this->getFollowHeader();
			$output .= Html::endTag('div');
		} elseif (in_array($this->tab, array_keys($this->getBadgeTabs()))) {
			$output .= Html::beginTag('div', ['class' => ['card-footer', 'py-2']]);
			$output .= $this->getBadgeHeader($this->list['user_badges']);
			$output .= Html::endTag('div');
		}
		$output .= Html::endTag('div');

		if ($this->tab == static::TAB_ABOUT) {
			$output .= $this->getAbout($this->list);
		} elseif ($this->tab == static::TAB_FOLLOW_DISCIPLINES) {
			$output .= $this->getDisciplines($this->list);
		} elseif ($this->tab == static::TAB_FOLLOW_USERS) {
			$output .= $this->getUsers($this->list);
		} elseif (in_array($this->tab, array_keys($this->getBadgeTabs()))) {
			$output .= $this->getBadges($this->list);
		} else {
			$output .= $this->getFeed($this->list);
		}

		$output .= Html::endTag('div');

		return $output;
	}

	protected function getAbout(array $list)
	{
		$items = [];
		if ($list['disciplines']) {
			$items[] = [
				'label' => Yii::t('app', 'Наибольшую пользу привнёс в предметы:'),
				'content' => $this->getDisciplines($list['disciplines']),
			];
		}
		if ($list['questions']) {
			$items[] = [
				'label' => Yii::t('app', 'Лучшие вопросы:'),
				'content' => $this->getFeed($list['questions']),
			];
		}
		if ($list['answers']) {
			$items[] = [
				'label' => Yii::t('app', 'Лучшие ответы:'),
				'content' => $this->getFeed($list['answers']),
			];
		}
		if ($list['badges']) {
			$items[] = [
				'label' => Yii::t('app', 'Главные достижения:'),
				'content' => $this->getBadges($list['badges'], true),
			];
		}

		$output = '';
		foreach ($items as $id => $params) {
			$output .= Html::beginTag('div', ['class' => ['card']]);
			$output .= Html::beginTag('div', ['class' => ['card-header']]);
			$output .= Html::tag('h3', $params['label'], ['class' => ['fw-bold']]);
			$output .= Html::endTag('div');
			$output .= Html::beginTag('div', ['class' => ['card-body', 'px-2']]);
			$output .= $params['content'];
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');
		}

		return $output;
	}

	protected function getFollowHeader()
	{
		return Html::ul($this->getFollowTabs(), [
			'tag' => 'div',
			'id' => 'follow-tabs',
			'class' => ['btn-group', 'w-100'],
			'role' => 'group',
			'item' => function ($item, $index) {
				$classes = ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'bi', 'bi_icon'];
				if ($index == $this->tab) {
					$classes[] = 'active';
					$classes[] = $item['class'].'-fill';
				} else {
					$classes[] = $item['class'];
				}
				return Html::a($item['text'], $item['link'], ['class' => $classes]);
			},
		]);
	}

	protected function getBadgeHeader(array $user_badges)
	{
		$counts = [
			static::TAB_BADGES => 0,
			static::TAB_BADGES_BRONZE => 0,
			static::TAB_BADGES_SILVER => 0,
			static::TAB_BADGES_GOLD => 0,
			static::TAB_BADGES_PLATINUM => 0,
		];
		foreach ($user_badges as $record) {
			$counts[static::TAB_BADGES]++;
			if ($record->badge->isBronze()) {
				$counts[static::TAB_BADGES_BRONZE]++;
			} elseif ($record->badge->isSilver()) {
				$counts[static::TAB_BADGES_SILVER]++;
			} elseif ($record->badge->isGold()) {
				$counts[static::TAB_BADGES_GOLD]++;
			} elseif ($record->badge->isPlatinum()) {
				$counts[static::TAB_BADGES_PLATINUM]++;
			}
		}
		return Html::ul($this->getBadgeTabs(), [
			'tag' => 'div',
			'id' => 'badge-tabs',
			'class' => ['btn-group', 'w-100'],
			'role' => 'group',
			'item' => function ($item, $index) use ($counts) {
				$text = $item['text'] . ' ' . HtmlHelper::getCountText($counts[$index], true);
				$classes = ['btn', 'btn-outline-info', 'btn-sm', 'w-100', 'bi', 'bi_icon'];
				$class = 'bi-award';
				if ($index == $this->tab) {
					$classes[] = 'active';
					$classes[] = $class.'-fill';
				} else {
					$classes[] = $class;
				}
				return Html::a($text, $item['link'], ['class' => $classes]);
			},
		]);
	}

	protected function getDisciplines(array $list)
	{
		$output = '';
		if ($list) {
			$output .= Html::beginTag('div', [
				'class' => [
					'row', 'row-cols-1', 'row-cols-sm-3', 'row-cols-md-2',
					'row-cols-lg-2', 'row-cols-xl-3', 'g-3', 'm-0', 'px-0'
				]
			]);
			foreach ($list as $record) {
				$output .= DisciplineRecord::widget([
					'model' => $record,
					'is_author' => true,
				]);
			}
			$output .= $this->getLinkPager();
			$output .= Html::endTag('div');
		} else {
			$output .= $this->getEmptyDiv();
		}
		return $output;
	}

	protected function getUsers(array $list)
	{
		$output = '';
		if ($list) {
			$output .= Html::beginTag('div', [
				'class' => [
					'row', 'row-cols-2', 'row-cols-sm-3', 'row-cols-md-2',
					'row-cols-lg-2', 'row-cols-xl-3', 'g-3', 'px-0', 'm-0'
				]
			]);
			foreach ($list as $record) {
				$output .= UserRecord::widget([
					'model' => $record->user
				]);
			}
			$output .= $this->getLinkPager();
			$output .= Html::endTag('div');
		} else {
			$output .= $this->getEmptyDiv();
		}
		return $output;
	}

	protected function getBadges(array $list, bool $is_about = false)
	{
		$name_list = Badge::getLevelNames();
		$output = '';
		if ($is_about) {
			$output .= $this->getBadgeListAbout($list);
		} elseif ($this->tab == static::TAB_BADGES) {
			foreach ($list['badges'] as $badge_level => $list_by_level) {
				$output .= Html::beginTag('div', ['class' => ['card']]);
				$output .= Html::beginTag('div', ['class' => ['card-header']]);
				$output .= Html::tag('h3', $name_list[$badge_level], ['class' => ['fw-bold']]);
				$output .= Html::endTag('div');
				$output .= Html::beginTag('div', ['class' => ['card-body']]);
				$output .= $this->getBadgeList($list, $badge_level);
				$output .= Html::endTag('div');
				$output .= Html::endTag('div');
			}
		} else {
			$badge_level = match ($this->tab) {
				static::TAB_BADGES_BRONZE => Badge::LEVEL_BRONZE,
				static::TAB_BADGES_SILVER => Badge::LEVEL_SILVER,
				static::TAB_BADGES_GOLD => Badge::LEVEL_GOLD,
				static::TAB_BADGES_PLATINUM => Badge::LEVEL_PLATINUM,
			};
			$output .= Html::beginTag('div', ['class' => ['card']]);
			$output .= Html::beginTag('div', ['class' => ['card-header']]);
			$output .= Html::tag('h3', $name_list[$badge_level], ['class' => ['fw-bold']]);
			$output .= Html::endTag('div');
			$output .= Html::beginTag('div', ['class' => ['card-body']]);
			$output .= $this->getBadgeList($list, $badge_level);
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');
		}
		return $output;
	}

	protected function getBadgeList(array $list, int $badge_level)
	{
		$output = '';
		$output .= Html::beginTag('div', [
			'class' => ['row', 'row-cols-2', 'row-cols-sm-3', 'row-cols-xl-4', 'px-2']
		]);
		foreach ($list['badges'][$badge_level] as $record) {
			$output .= BadgeRecord::widget([
				'model' => $record,
				'data' => $list['user_badge_data'],
			]);
		}
		$output .= Html::endTag('div');
		return $output;
	}

	protected function getBadgeListAbout(array $list)
	{
		$output = '';
		$output .= Html::beginTag('div', [
			'class' => ['row', 'row-cols-2', 'row-cols-sm-3', 'row-cols-xl-4', 'px-2']
		]);
		foreach ($list['user_badges'] as $record) {
			$output .= BadgeRecord::widget([
				'model' => $record->badge,
				'data' => $list['user_badge_data'],
			]);
		}
		$output .= Html::endTag('div');
		return $output;
	}

	protected function getFeed(array $list)
	{
		$report_types = ReportType::getListsByType();
		$output = '';
		if ($list) {
			foreach ($list as $record) {
				$output .= FeedRecord::widget([
					'model' => $record,
					'report_types' => $report_types,
				]);
			}
			$output .= $this->getLinkPager();
		} else {
			$output .= $this->getEmptyDiv();
		}
		return $output;
	}

	protected function getLinkPager()
	{
		$output = '';
		if (!is_null($this->pages) and ($this->pages->totalCount > $this->pages->limit)) {
			$output .= Html::beginTag('div', ['class' => ['card', 'card-body', 'mt-3']]);
			$output .= Html::beginTag('div', ['class' => ['row']]);
			$output .= LinkPager::widget([
				'pagination' => $this->pages,
				'registerLinkTags' => true
			]);
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');
		}
		return $output;
	}

	protected function getPagination(array $list)
	{
		return new Pagination([
			'totalCount' => count($list),
			'defaultPageSize' => 10,
		]);
	}

	protected function getEmptyDiv()
	{
		return Html::tag('div',
			Yii::t('app', 'Записей не было найдено'),
			['class' => ['h2', 'text-center', 'mt-3']]
		);
	}

}
