<?php

namespace app\widgets\discipline;

use Yii;
use yii\bootstrap5\{Accordion, Html, LinkPager, Widget};
use yii\data\Pagination;

use app\models\question\{Question, Answer, Comment};
use app\models\request\ReportType;
use app\models\badge\{Badge, UserBadge};
use app\models\user\{User, UserData};
use app\models\edu\Discipline;
use app\models\data\RecordList;
use app\models\helpers\HtmlHelper;

use app\widgets\discipline\DisciplineRecord;
use app\widgets\question\FeedRecord;
use app\widgets\user\UserRecord;

use app\assets\actions\DisciplineViewAsset;


class ProfileRecord extends Widget
{

	public Discipline $model;
	public string $tab;
	protected array $list;
	protected ?Pagination $pages = null;

	protected const TAB_ABOUT = 'about';
	protected const TAB_QUESTIONS = 'questions';
	protected const TAB_ANSWERS = 'answers';
	protected const TAB_COMMENTS = 'comments';
	protected const TAB_USERS = 'users';
	protected const TAB_TAGS = 'tags';
	protected const TAB_FOLLOWERS = 'followers';
	protected const TAB_CHAIR_DISCIPLINES = 'chair-disciplines';
	protected const TAB_WORKS = 'works';


	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if (!in_array($this->tab, $this->getProfileSectionNames())) {
			$this->tab = static::TAB_ABOUT;
		}
		DisciplineViewAsset::register($this->view);
		return true;
	}

	public function run()
	{
		$this->view->title = Yii::t('app', 'Предмет {name}', [
			'name' => $this->model->name
		]);

		$this->loadList();
		$output = '';
		$output .= $this->getHeader();
		$output .= $this->getDisciplineInfo();
		$output .= $this->getBody();
		return $output;
	}

	protected function getProfileSectionNames(): array
	{
		return array_keys($this->getProfileTabs());
	}

	protected function getProfileTabs(): array
	{
		$anchor = '#tabs';
		return [
			static::TAB_ABOUT => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Описание')),
				'link' => $this->model->getPageLink() . $anchor,
				'class' => 'bi-card-text'
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
			static::TAB_USERS => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Пользователи')),
				'link' => $this->model->getPageLink(tab: static::TAB_USERS) . $anchor,
				'class' => 'bi-people'
			],
			static::TAB_FOLLOWERS => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Подписчики')),
				'link' => $this->model->getPageLink(tab: static::TAB_FOLLOWERS) . $anchor,
				'class' => 'bi-bell'
			],
			static::TAB_TAGS => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Теги')),
				'link' => $this->model->getPageLink(tab: static::TAB_TAGS) . $anchor,
				'class' => 'bi-tags'
			],
			static::TAB_CHAIR_DISCIPLINES => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Похожие предметы')),
				'link' => $this->model->getPageLink(tab: static::TAB_CHAIR_DISCIPLINES) . $anchor,
				'class' => 'bi-book'
			],
			// static::TAB_WORKS => [
			// 	'text' => HtmlHelper::getIconText(Yii::t('app', 'Работы')),
			// 	'link' => $this->model->getPageLink(tab: static::TAB_WORKS) . $anchor,
			// 	'class' => 'bi-pen'
			// ],
		];
	}

	protected function loadList()
	{
		if ($this->tab == static::TAB_ABOUT) {
			$this->list = [
				static::TAB_USERS => RecordList::getDisciplineUserBestList($this->model->id, 5),
				static::TAB_QUESTIONS => RecordList::getQuestionDisciplineBestListAbout($this->model->id, 5),
				static::TAB_ANSWERS => RecordList::getAnswerDisciplineBestListAbout($this->model->id, 5),
				static::TAB_TAGS => RecordList::getDisciplineQuestionTagCounts($this->model->id, 6),
				static::TAB_CHAIR_DISCIPLINES => RecordList::getDisciplineChairList($this->model->id, 5),
			];
		} elseif ($this->tab == static::TAB_QUESTIONS) {
			list($this->list, $this->pages) = RecordList::getQuestionDisciplineBestList($this->model->id);
		} elseif ($this->tab == static::TAB_ANSWERS) {
			list($this->list, $this->pages) = RecordList::getAnswerDisciplineBestList($this->model->id);
		} elseif ($this->tab == static::TAB_COMMENTS) {
			list($this->list, $this->pages) = RecordList::getCommentDisciplineBestList($this->model->id);
		} elseif ($this->tab == static::TAB_USERS) {
			$this->list = RecordList::getDisciplineUserBestList($this->model->id, 100);
			$this->pages = $this->getPagination($this->list, 10);
			$this->list = array_slice($this->list, $this->pages->offset, $this->pages->limit);
		} elseif ($this->tab == static::TAB_FOLLOWERS) {
			$this->list = Discipline::getFollowerList($this->model->id);
			$this->pages = $this->getPagination($this->list, 10);
			$this->list = array_slice($this->list, $this->pages->offset, $this->pages->limit);
		} elseif ($this->tab == static::TAB_TAGS) {
			$this->list = RecordList::getDisciplineQuestionTagCounts($this->model->id);
		} elseif ($this->tab == static::TAB_CHAIR_DISCIPLINES) {
			$this->list = RecordList::getDisciplineChairList($this->model->id);
			$this->pages = $this->getPagination($this->list, 12);
			$this->list = array_slice($this->list, $this->pages->offset, $this->pages->limit);
		}
	}

	protected function getHeader()
	{
		$output = '';
		$output .= Html::beginTag('div', ['class' => 'col-sm-12 mb-2']);

		$output .= Html::beginTag('div', ['class' => ['card', 'py-3']]);
		$output .= Html::tag('div', null, ['class' => ['rounded-top']]);
		$output .= Html::beginTag('div', ['class' => ['card-body', 'py-0']]);
		$output .= Html::beginTag('div', ['class' => ['d-sm-flex', 'align-items-center', 'text-center', 'text-sm-start']]);
		$output .= Html::beginTag('div');
		$output .= Html::beginTag('div', ['id' => 'avatar', 'class' => ['avatar', 'avatar-xxl', 'mb-3']]);
		$output .= Html::img($this->model->getImgLink(), ['class' => ['avatar-img', 'rounded', 'avatar-image', 'bg-white']]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['ms-sm-4', 'mb-0', 'h5', 'profile-name']]);
		$output .= $this->model->name;
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$link = $this->model->getPageLink(true);
		$output .= HtmlHelper::getShareDiv('share-profile', $link, Yii::t('app', 'Ссылка на профиль'));
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');

		return $output;
	}

	protected function getDisciplineInfo()
	{
		$output = '';
		$output .= Html::beginTag('div', ['class' => ['col-sm-12', 'col-lg-3', 'gap-2']]);

		$output .= Html::beginTag('div', ['class' => ['card']]);
		$output .= Html::beginTag('div', ['class' => ['card-body', 'text-center']]);
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::tag('h6', Yii::t('app', 'Подписчиков'), ['class' => ['mb-0']]);
		$output .= Html::tag('h3', $this->model->followers, ['class' => ['mb-0', 'text-info', 'fw-bold']]);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::tag('hr', null, ['class' => ['my-1']]);
		$output .= Html::tag('h6', Yii::t('app', 'Вопросов'), ['class' => ['mb-0']]);
		$output .= Html::tag('h3', $this->model->question_count, ['class' => ['mb-0', 'text-warning', 'fw-bold']]);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::tag('hr', null, ['class' => ['my-1']]);
		$output .= Html::tag('h6', Yii::t('app', 'Решений'), ['class' => ['mb-0']]);
		$output .= Html::tag('h3', $this->model->question_helped_count, ['class' => ['mb-0', 'text-success', 'fw-bold']]);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::tag('hr', null, ['class' => ['my-1']]);
		$output .= Html::tag('h6', Yii::t('app', 'Ответов'), ['class' => ['mb-0']]);
		$output .= Html::tag('h3', $this->model->answer_count, ['class' => ['mb-0', 'text-danger', 'fw-bold']]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['id' => 'profile-buttons', 'class' => ['card', 'my-2']]);
		$output .= Html::beginTag('div', ['class' => ['card-body']]);

		$output .= Html::beginTag('div', ['class' => ['w-100', 'mb-3']]);
		if ($this->model->isFollowed) {
			$title = HtmlHelper::getIconText(Yii::t('app', 'Вы подписаны'), true);
			$output .= HtmlHelper::actionButton($title, 'unfollow-discipline', $this->model->id, [
				'class' => ['btn', 'btn-light', 'text-secondary', 'w-100', 'bi', 'bi-bell-fill', 'bi_icon', 'me-2'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Отписаться от предмета')
			]);
		} else {
			$title = HtmlHelper::getIconText(Yii::t('app', 'Подписаться'), true);
			$output .= HtmlHelper::actionButton($title, 'follow-discipline', $this->model->id, [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'w-100', 'bi', 'bi-bell', 'bi_icon', 'me-2'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Подписаться на предмет')
			]);
		}
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['w-100']]);
		$output .= Html::button(Yii::t('app', 'Поделиться'), [
			'id' => 'share-profile',
			'class' => ['btn', 'btn-outline-light', 'btn-share-link', 'text-secondary', 'w-100', 'bi', 'bi-share-fill', 'bi_icon'],
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
			'id' => 'discipline-tabs',
			'class' => [
				'nav', 'nav-bottom-line', 'align-items-center',
				'd-flex', 'justify-content-between', 'mb-0', 'border-0'
			],
			'item' => function ($item, $index) {
				$classes = ['nav-link', 'bi', 'bi_icon', 'px-1'];
				if ($index == $this->tab) {
					$classes[] = 'active';
				}
				if (($index == $this->tab) and ($index !== static::TAB_ABOUT)) {
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
		$output .= Html::endTag('div');

		if ($this->tab == static::TAB_ABOUT) {
			$output .= $this->getAbout($this->list);
		} elseif ($this->tab == static::TAB_USERS) {
			$output .= $this->getUsers($this->list);
		} elseif ($this->tab == static::TAB_FOLLOWERS) {
			$output .= $this->getFollowers($this->list);
		} elseif ($this->tab == static::TAB_TAGS) {
			$output .= $this->getTags($this->list);
		} elseif ($this->tab == static::TAB_CHAIR_DISCIPLINES) {
			$output .= $this->getDisciplines($this->list);
		} else {
			$output .= $this->getFeed($this->list);
		}

		$output .= Html::endTag('div');

		return $output;
	}

	protected function getAbout(array $list)
	{
		$items = [];
		if ($list[static::TAB_TAGS]) {
			$items[] = [
				'label' => Yii::t('app', 'Теги предмета:'),
				'content' => $this->getTags($list[static::TAB_TAGS]),
			];
		}
		if ($list[static::TAB_USERS]) {
			$items[] = [
				'label' => Yii::t('app', 'Наибольшую пользу привнесли в предмет:'),
				'content' => $this->getUsers($list[static::TAB_USERS]),
			];
		}
		if ($list[static::TAB_QUESTIONS]) {
			$items[] = [
				'label' => Yii::t('app', 'Лучшие вопросы:'),
				'content' => $this->getFeed($list[static::TAB_QUESTIONS]),
			];
		}
		if ($list[static::TAB_ANSWERS]) {
			$items[] = [
				'label' => Yii::t('app', 'Лучшие ответы:'),
				'content' => $this->getFeed($list[static::TAB_ANSWERS]),
			];
		}
		if ($list[static::TAB_CHAIR_DISCIPLINES]) {
			$items[] = [
				'label' => Yii::t('app', 'Похожие предметы:'),
				'content' => $this->getDisciplines($list[static::TAB_CHAIR_DISCIPLINES]),
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
			$user_ids = array_column($list, 'user_id');
			$question_counts = RecordList::getUserDisciplineQuestionCounts($user_ids, $this->model->id);
			$answer_counts = RecordList::getUserDisciplineAnswerCounts($user_ids, $this->model->id);
			$answer_helped_counts = RecordList::getUserDisciplineAnswerHelpedCounts($user_ids, $this->model->id);
			$output .= Html::beginTag('div', [
				'class' => [
					'row', 'row-cols-2', 'row-cols-sm-3', 'row-cols-md-2',
					'row-cols-lg-2', 'row-cols-xl-3', 'g-3', 'px-0', 'm-0'
				]
			]);
			foreach ($list as $record) {
				$output .= UserRecord::widget([
					'model' => $record,
					'question_count' => $question_counts[$record->id] ?? 0,
					'answer_count' => $answer_counts[$record->id] ?? 0,
					'answer_helped_count' => $answer_helped_counts[$record->id] ?? 0,
				]);
			}
			$output .= $this->getLinkPager();
			$output .= Html::endTag('div');
		} else {
			$output .= $this->getEmptyDiv();
		}
		return $output;
	}

	protected function getFollowers(array $list)
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
					'model' => $record->follower
				]);
			}
			$output .= $this->getLinkPager();
			$output .= Html::endTag('div');
		} else {
			$output .= $this->getEmptyDiv();
		}
		return $output;
	}

	protected function getTags(array $list)
	{
		$output = '';
		if ($list) {
			$output .= Html::beginTag('div', [
				'class' => [
					'row', 'row-cols-1', 'row-cols-sm-3', 'row-cols-md-2',
					'row-cols-lg-2', 'row-cols-xl-3', 'g-3', 'px-0', 'm-0'
				]
			]);
			foreach ($list as $record) {
				$output .= Html::beginTag('div', ['class' => ['cols', 'px-1']]);
				$output .= Html::a(
					HtmlHelper::getIconText($record->name, true) .
					HtmlHelper::getCountText($record->filter_question_count, true),
					$record->getRecordLink(),
					['class' => ['badge', 'rounded-pill', 'border', 'border-info', 'bg-light', 'text-secondary', 'w-100']]
				);
				$output .= Html::endTag('div');
			}
			$output .= Html::endTag('div');
		} else {
			$output .= $this->getEmptyDiv();
		}
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

	protected function getPagination(array $list, int $page_size = 10)
	{
		if ($page_size <= 0) {
			$page_size = 10;
		}
		return new Pagination([
			'totalCount' => count($list),
			'defaultPageSize' => $page_size,
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
