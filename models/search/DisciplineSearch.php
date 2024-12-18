<?php

namespace app\models\search;

use Yii;
use yii\data\{ArrayDataProvider, Pagination};

use app\models\edu\Discipline;
use app\models\helpers\{TextHelper, UserHelper};


class DisciplineSearch extends Discipline
{

	public $text;
	public $sort;

	private $_pages;
	private $_current_semestr;
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

	private const QUESTIONS = 'question_count';
	private const HELPED = 'question_helped_count';
	private const ANSWERS = 'answer_count';
	private const FOLLOW = 'followers';
	private const IS_FOLLOW = 'isFollowed';
	private const NAME = 'name';

	private const STANDART = 'standart';

	public function search($params)
	{
		$this->_current_semestr = isset($params['current_semestr']);
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

	public function getSortList() {
		$standart = [
			static::STANDART => Yii::t('app', 'По релевантности'),
		];
		$desc = [
			static::NAME => Yii::t('app', 'В алфавитном порядке'),
			'-' . static::QUESTIONS => Yii::t('app', 'Больше вопросов'),
			'-' . static::HELPED => Yii::t('app', 'Больше решений'),
			'-' . static::ANSWERS => Yii::t('app', 'Больше ответов'),
			'-' . static::FOLLOW => Yii::t('app', 'Больше подписчиков'),
		];
		$asc = [
			// '-' . static::NAME => Yii::t('app', 'В обратном алфавитном порядке'),
			// static::QUESTIONS => Yii::t('app', 'Больше вопросов в конце'),
			// static::HELPED => Yii::t('app', 'Больше решений в конце'),
			// static::FOLLOW => Yii::t('app', 'Больше подписчиков в конце'),
		];
		// if ($type == 1) {
		//     return $asc;
		// }
		// if ($type == -1) {
		//     return $desc;
		// }
		return array_merge($standart, $desc, $asc);
	}

	public function getProvider()
	{
		$query = $this->getQuery();
		$queryCount = clone $query;
		$this->_pages = new Pagination([
		    'totalCount' => $queryCount->count(),
		    'defaultPageSize' => 12,
		]);
		$list = $query->all();

		return new ArrayDataProvider([
			'allModels' => $list,
			'sort' => [
				'attributes' => [
					static::IS_FOLLOW,
					static::QUESTIONS => [
						'asc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::QUESTIONS => SORT_ASC
						],
						'desc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::QUESTIONS => SORT_DESC
						]
					],
					static::HELPED => [
						'asc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::HELPED => SORT_ASC
						],
						'desc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::HELPED => SORT_DESC
						]
					],
					static::ANSWERS => [
						'asc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::ANSWERS => SORT_ASC
						],
						'desc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::ANSWERS => SORT_DESC
						]
					],
					static::FOLLOW => [
						'asc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::FOLLOW => SORT_ASC
						],
						'desc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::FOLLOW => SORT_DESC
						]
					],
					static::NAME => [
						'asc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::NAME => SORT_ASC
						],
						'desc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::NAME => SORT_DESC
						]
					],
					static::STANDART => [
						'asc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::QUESTIONS => SORT_DESC,
							static::HELPED => SORT_DESC,
							static::ANSWERS => SORT_DESC,
							static::FOLLOW => SORT_DESC,
							static::NAME => SORT_ASC,
						],
						'desc' => [
							static::IS_FOLLOW => SORT_DESC,
							static::QUESTIONS => SORT_DESC,
							static::HELPED => SORT_DESC,
							static::ANSWERS => SORT_DESC,
							static::FOLLOW => SORT_DESC,
							static::NAME => SORT_ASC,
						]
					],
				],
				'defaultOrder' => $this->getOrder(),
			],
			'pagination' => [
				'pageSize' => $this->_pages->limit,
			],
		]);
	}

	public function getQuery()
	{
		$user_id = Yii::$app->user->identity->id;
		$query = Discipline::find()
			->distinct()
			->from(['discipline' => Discipline::tableName()])
			->joinWith('follow as follow')
			->joinWith('icon as icon');
		if ($this->_current_semestr) {
			$year = UserHelper::getYear();
			$semestr = UserHelper::getSemestr();
			$query->joinWith('toGroup as tg')
				->joinWith('groups.students')
				->where(['user_id' => $user_id])
				->andWhere(['tg.year' => $year])
				->andWhere(['tg.semestr' => $semestr]);
		}
		if ($this->_follows) {
			$query->andWhere(['follow.follower_id' => $user_id]);
		}
		$this->text = TextHelper::remove_multiple_whitespaces($this->text);
		if ($this->text) {
			$strings = explode(' ', $this->text);
			foreach ($strings as $string) {
				$string = TextHelper::remove_word_end($string);
				$query->andFilterWhere(['LIKE', 'discipline_name', $string]);
			}
		}
		return $query;
	}

	public function isEmpty()
	{
		return !$this->text and ($this->sort == static::STANDART);
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
