<?php

namespace app\models\search;

use Yii;
use yii\data\{ArrayDataProvider, Pagination, Sort};
use yii\helpers\ArrayHelper;

use app\models\user\User;
use app\models\helpers\TextHelper;


class UserSearch extends User
{

	public $text;
	public $sort;

	private $_pages;
	private $_follows;

	public function rules()
	{
		return [
			[['text', 'sort'], 'safe'],
			[['text'], 'string'],
			[['text'], 'trim'],
			[['sort'], 'in', 'range' => array_keys($this->getSortList())],

			[['text'], 'filter', 'filter' => 'strip_tags'],
		];
	}

	public function attributeLabels()
	{
		return [
			'text' => Yii::t('app', 'Текст'),
			'sort'  => Yii::t('app','Сортировка'),
		];
	}

	private const RATE_SUM = 'rate_sum';
	private const QUESTIONS = 'question_count';
	private const ANSWERS = 'answer_count';
	private const LIKE = 'answer_like_count';
	private const HELPED = 'answer_helped_count';
	private const REGISTER = 'register_datetime';
	private const IS_FOLLOW = 'isFollowed';
	private const IS_SELF = 'isSelf';
	private const NAME = 'name';

	private const STANDART = 'standart';

	public function search($params)
	{
		$this->_follows = isset($params['follows']);
		$this->load($params);
		if (!$this->validate()) {
			$this->clearFields();
		}
		if (!$this->sort) {
			$this->sort = static::STANDART;
		}
		return $this->getProvider();
	}

	public function getPages() {
		return $this->_pages;
	}

	public function getSortList(int $type = 0) {
		$standart = [
			static::STANDART => Yii::t('app', 'По релевантности'),
		];
		$desc = [
			'-' . static::RATE_SUM => Yii::t('app', 'Больше рейтинг'),
			'-' . static::QUESTIONS => Yii::t('app', 'Больше вопросов'),
			'-' . static::ANSWERS => Yii::t('app', 'Больше ответов'),
			'-' . static::LIKE => Yii::t('app', 'Более полезные ответы'),
			'-' . static::HELPED => Yii::t('app', 'Больше решений'),
			// '-' . static::REGISTER => Yii::t('app', 'Раньше присоединились'),
			static::NAME => Yii::t('app', 'В алфавитном порядке'),
		];
		$asc = [
			// static::RATE_SUM => Yii::t('app', 'Больше рейтинг в конце'),
			// static::QUESTIONS => Yii::t('app', 'Больше вопросов в конце'),
			// static::ANSWERS => Yii::t('app', 'Больше ответов в конце'),
			// static::LIKE => Yii::t('app', 'Наиболее полезные в конце'),
			// static::HELPED => Yii::t('app', 'Больше решений в конце'),
			// static::REGISTER => Yii::t('app', 'Раньше присоединились в конце'),
			// '-' . static::NAME => Yii::t('app', 'В обратном алфавитном порядке'),
		];
		// if ($type == 1) {
		// 	return $asc;
		// }
		// if ($type == -1) {
		// 	return $desc;
		// }
		return array_merge($standart, $desc, $asc);
	}

	public function getProvider()
	{
		$query = $this->getQuery();
		$queryCount = clone $query;
		$this->_pages = new Pagination([
			'totalCount' => $queryCount->count(),
			'defaultPageSize' => 12
		]);
		$list = $query->all();

		return new ArrayDataProvider([
			'allModels' => $list,
			'sort' => new Sort([
				'attributes' => [
					static::RATE_SUM => [
						'asc' => [
							'data.' . static::RATE_SUM => SORT_ASC
						],
						'desc' => [
							'data.' . static::RATE_SUM => SORT_DESC
						]
					],
					static::QUESTIONS => [
						'asc' => [
							'data.badgeData.' . static::QUESTIONS => SORT_ASC
						],
						'desc' => [
							'data.badgeData.' . static::QUESTIONS => SORT_DESC
						]
					],
					static::ANSWERS => [
						'asc' => [
							'data.badgeData.' . static::ANSWERS => SORT_ASC
						],
						'desc' => [
							'data.badgeData.' . static::ANSWERS => SORT_DESC
						]
					],
					static::LIKE => [
						'asc' => [
							'data.badgeData.' . static::LIKE => SORT_ASC
						],
						'desc' => [
							'data.badgeData.' . static::LIKE => SORT_DESC
						]
					],
					static::HELPED => [
						'asc' => [
							'data.badgeData.' . static::HELPED => SORT_ASC
						],
						'desc' => [
							'data.badgeData.' . static::HELPED => SORT_DESC
						]
					],
					/*static::REGISTER => [
						'asc' => [
							'register.' . static::REGISTER => SORT_ASC
						],
						'desc' => [
							'register.' . static::REGISTER => SORT_DESC
						]
					],*/
					static::NAME,
					static::STANDART => [
						'asc' => [
							'data.' . static::IS_SELF => SORT_DESC,
							'data.' . static::IS_FOLLOW => SORT_DESC,
							'dg.group_id' => SORT_DESC,
							'data.' . static::RATE_SUM => SORT_DESC,
							'data.badgeData.' . static::QUESTIONS => SORT_DESC,
							'data.badgeData.' . static::HELPED => SORT_DESC,
							static::NAME => SORT_ASC,
						],
						'desc' => [
							'data.' . static::IS_SELF => SORT_DESC,
							'data.' . static::IS_FOLLOW => SORT_DESC,
							'dg.group_id' => SORT_DESC,
							'data.' . static::RATE_SUM => SORT_ASC,
							'data.badgeData.' . static::QUESTIONS => SORT_ASC,
							'data.badgeData.' . static::HELPED => SORT_ASC,
							static::NAME => SORT_ASC,
						]
					],
				],
				'defaultOrder' => $this->getOrder(),
			]),
			'pagination' => [
				'pageSize' => $this->_pages->limit,
			],
		]);
	}

	public function getQuery()
	{
		$user = Yii::$app->user->identity;
		$user_id = $user->id;
		$group_id = $user->data->group_id;
		$query = User::find()
			->distinct()
			->from(['user' => User::tableName()])
			->innerJoinWith('data as data')
			->joinwith('data.badgeData')
			->joinwith('data.follow as follow')
			->joinwith(['data.group as dg' => function($query) use($group_id) {
				return $query->onCondition(['dg.group_id' => $group_id]);
			}]);

		if ($this->_follows) {
			$query->andWhere(['follow.follower_id' => $user_id]);
		}
		$this->text = TextHelper::remove_multiple_whitespaces($this->text);
		if ($this->text) {
			$query->andFilterWhere(['OR',
				['LIKE', 'username', $this->text],
				['LIKE', "CONCAT(`first_name`, ' ', `last_name`)", $this->text],
				['LIKE', "CONCAT(SUBSTRING(`first_name`, 1, 1), `last_name`)", $this->text],
			]);
		}
		return $query;
	}

	public function clearFields()
	{
		if ($this->errors) {
			foreach ($this->errors as $attribute => $value) {
				$this->{$attribute} = null;
				$this->clearErrors($attribute);
			}
		}
	}

	public function getOrder()
	{
		$field = static::STANDART;
		$order = SORT_ASC;
		if ($this->sort) {
			if (mb_substr($this->sort, 0, 1) == '-') {
				$field = mb_substr($this->sort, 1);
				$order = SORT_DESC;
			} else {
				$field = $this->sort;
				$order = SORT_ASC;
			}
		}
		return [$field => $order];
	}

	public function getSortField()
	{
		if (mb_substr($this->sort, 0, 1) == '-') {
			return mb_substr($this->sort, 1);
		}
		return $this->sort;
	}


}
