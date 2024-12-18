<?php

namespace app\models\search;

use Yii;
use yii\data\{ArrayDataProvider, Pagination, Sort};
use yii\helpers\ArrayHelper;

use app\models\question\Tag;
use app\models\helpers\TextHelper;


class TagSearch extends Tag
{

	public $text;
	public $discipline_name;
	public $parent_id;

	private $_pages;

	public function rules()
	{
		return [
			[['text', 'discipline_name', 'parent_id'], 'safe'],
			[['text', 'discipline_name'], 'string'],
			[['parent_id'], 'integer'],

			[['text'], 'trim'],
			[['text'], 'filter', 'filter' => 'strip_tags'],
		];
	}

	public function attributeLabels()
	{
		return [
			'text' => Yii::t('app', 'Текст'),
			'discipline_name'  => Yii::t('app', 'Дисциплина'),
			'parent_id'  => Yii::t('app', 'Родительский тег'),
		];
	}

	public function search($params)
	{
		$this->load($params);
		if (!$this->validate()) {
			$this->clearFields();
		}
		return $this->getProvider();
	}

	public function getPages() {
		return $this->_pages;
	}

	public function getParent() {
		return $this->hasOne(Tag::class, ['tag_id' => 'parent_id']);
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
					'tag_name',
					'discipline_name' => [
						'asc' => [
							'discipline.discipline_name' => SORT_ASC
						],
						'desc' => [
							'discipline.discipline_name' => SORT_DESC
						]
					],
				],
				'defaultOrder' => 'tag_name',
			]),
			'pagination' => [
				'pageSize' => $this->_pages->limit,
			],
		]);
	}

	public function getQuery()
	{
		$query = Tag::find()
			->from(['tag' => Tag::tableName()])
			->joinWith('discipline as d')
			// ->where(['IS', 'parent_id', NULL])
			;
		$this->text = TextHelper::remove_multiple_whitespaces($this->text);
		$this->discipline_name = TextHelper::remove_multiple_whitespaces($this->discipline_name);
		if ($this->text) {
			$query->andFilterWhere(['LIKE', 'tag_name', $this->text]);
		}
		if ($this->discipline_name) {
			$query->andFilterWhere(['LIKE', 'd.discipline_name', $this->discipline_name]);
		}
		if ($this->parent_id) {
			$parent = $this->parent;
			if ($parent) {
				$child_ids = $parent->getFullChildrenList();
				$query->andFilterWhere(['IN', 'tag_id', array_keys($child_ids)]);
			}
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

}
