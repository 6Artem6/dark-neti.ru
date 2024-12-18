<?php

namespace app\models\question;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

use app\models\edu\Discipline;
use app\models\helpers\{ModelHelper, TextHelper};


class Tag extends ActiveRecord
{

	public static function tableName()
	{
		return 'tag';
	}

	public function rules()
	{
		return [
			[['!tag_id'], 'unique'],
			[['tag_id', 'parent_id', 'discipline_id'], 'integer'],
			[['tag_name'], 'string', 'max' => 256],
			[['discipline_name'], 'string'],
			[['!is_checked'], 'boolean'],

			[['tag_name'], 'filter', 'filter' => 'strip_tags'],
			[['tag_name'], 'filter', 'filter' => [TextHelper::class, 'remove_multiple_whitespaces']],
			[['tag_name'], 'filter', 'filter' => [TextHelper::class, 'remove_emoji']],
			[['tag_name'], 'checkName'],
			[['parent_id'], 'checkParent'],

			[['discipline_id', 'tag_name'], 'required'],
		];
	}

	public static function primaryKey()
	{
		return [
			'tag_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'tag_id' => Yii::t('app', 'Тег'),
			'tag_name' => Yii::t('app', 'Тег'),
			'parent_id' => Yii::t('app', 'Родительский тег'),
			'is_checked' => Yii::t('app', 'Тег проверен'),
			'discipline_id' => Yii::t('app', 'Дисциплина'),
			'discipline_name' => Yii::t('app', 'Дисциплина'),
		];
	}

	public $discipline_name;

	private int $_filter_question_count = 0;

	public const SCENARIO_STUDENT = 'user';
	public const SCENARIO_MODERATOR = 'moderator';

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);

		parent::init();
	}

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			static::SCENARIO_STUDENT => [
				'discipline_name', 'tag_name', 'is_checked'
			],
			static::SCENARIO_MODERATOR => [
				'parent_id', 'discipline_id', 'tag_name', 'is_checked'
			],
		]);
	}

	public function checkName($attribute)
	{
		if ($this->discipline_id == -1) {
			$this->discipline_id = 0;
		}
		if (!$this->discipline_id) {
			return true;
		}
		$this->checkDiscipline();
		$discipline = Discipline::findOne($this->discipline_id);
		$discipline_id = $discipline->discipline_id;
		$exists = self::find()
			->where(['discipline_id' => $discipline_id])
			->andWhere(['tag_name' => $this->tag_name])
			->one();
		if ($this->isNewRecord) {
			if ($exists) {
				$message = Yii::t('app', 'Тег с таким названием уже создан в данной дисциплине');
				$this->addError('tag_name', $message);
				Yii::$app->session->setFlash('error', $message);
				return false;
			}
		} else {
			if ($this->tag_id != $exists->tag_id) {
				$message = Yii::t('app', 'Тег с таким названием уже создан в данной дисциплине');
				$this->addError('tag_name', $message);
				Yii::$app->session->setFlash('error', $message);
				return false;
			}
		}
		return true;
	}

	public function checkParent($attribute)
	{
		if (empty($this->parent_id)) {
			return true;
		}
		$parent = self::findOne(['tag_id' => $this->parent_id]);
		if (!$parent) {
			$message = Yii::t('app', 'Родительский тег не был найден');
			$this->addError('parent_id', $message);
			Yii::$app->session->setFlash('error', $message);
			return false;
		}
		if (!$this->isNewRecord) {
			if ($this->parent_id == $this->tag_id) {
				$message = Yii::t('app', 'Тег не может быть своим родительским тегом');
				$this->addError('parent_id', $message);
				Yii::$app->session->setFlash('error', $message);
				return false;
			}
			$parent_id = $this->parent_id;
			do {
				$parent = self::findOne(['tag_id' => $parent_id]);
				if (!empty($parent)) {
					if ($this->tag_id == $parent->parent_id) {
						$message = Yii::t('app', 'Родительский тег не может быть из списка подтегов');
						$this->addError('parent_id', $message);
						Yii::$app->session->setFlash('error', $message);
						return false;
					}
					$parent_id = $parent->parent_id;
				}
			} while (!empty($parent));
		}
		return true;
	}

	protected function checkBeforeValidate($event)
	{
		if ($this->isNewRecord) {
			$this->checkDiscipline();
			if (!empty($this->parent_id)) {
				$parent = self::findOne(['tag_id' => $this->parent_id]);
				$this->discipline_id = $parent->discipline_id;
			}
			if (($this->scenario == static::SCENARIO_MODERATOR) and !$this->is_checked) {
				$this->is_checked = true;
			}
		}
	}

	protected function checkDiscipline()
	{
		if (empty($this->parent_id) and !empty($this->discipline_name)) {
			$discipline = $this->disciplineModel->checkRecord($this->discipline_name);
			$this->discipline_id = $discipline->discipline_id;
		}
	}

	public function checkRecordStudent(string $tag, int $discipline_id)
	{
		$record = self::findOne(['tag_name' => $tag, 'discipline_id' => $discipline_id]);
		if (empty($record)) {
			$record = new self(['scenario' => static::SCENARIO_STUDENT]);
			$record->tag_name = $tag;
			$record->discipline_id = $discipline_id;
			$record->is_checked = false;
			$record->save();
		}
		return $record;
	}

	public function getDiscipline()
	{
		return $this->hasOne(Discipline::class, ['discipline_id' => 'discipline_id']);
	}

	public function getQuestions()
	{
		return $this->hasOne(Question::class, ['question_id' => 'question_id'])
			->via('toQuestion');
	}

	public function getToQuestion()
	{
		return $this->hasOne(TagToQuestion::class, ['tag_id' => 'tag_id']);
	}

	public function getId()
	{
		return $this->tag_id;
	}

	public function getName()
	{
		return $this->tag_name;
	}

	public function getFilter_question_count()
	{
		return $this->_filter_question_count;
	}

	public function setFilter_question_count(int $value)
	{
		$this->_filter_question_count = $value;
	}

	public static function getList()
	{
		return self::find()->select(['tag_name'])->indexBy('tag_id')->column();
	}

	public static function getParentList()
	{
		return self::find()
			->select(['tag_name'])
			->where(['IS', 'parent_id', NULL])
			->indexBy('tag_id')
			->column();
	}

	public function getFirstChildrenList()
	{
		return self::find()
			->select(['tag_name'])
			->where(['parent_id' => $this->tag_id])
			->indexBy('tag_id')
			->column();
	}

	public function getChildrenCount()
	{
		return count($this->getFullChildrenList());
	}

	public function getFullChildrenList(int $limit = 0)
	{
		$list = [];
		if ($limit < 0) {
			$limit = 0;
		}
		$list = [$this->id => $this->name];
		$tag_list = self::find()
			->where(['discipline_id' => $this->discipline_id])
			->indexBy('tag_id')
			->all();
		foreach ($tag_list as $id => $tag) {
			if (in_array($tag->parent_id, array_keys($list))) {
				$list[] = [$id => $tag->name];
				if ($limit and (count($list) >= $limit)) {
					break;
				}
			}
		}
		unset($list[$this->id]);
		return $list;
	}

	public function getFullChildrenListRecursive(int $limit = 0)
	{
		if ($limit < 0) {
			$limit = 0;
		}
		$list = [];
		$tag_list = [$this->id];
		foreach ($tag_list as $id) {
			$child_list = self::find()
				->select(['tag_name'])
				->where(['parent_id' => $this->id])
				->indexBy('tag_id')
				->column();
			$tag_list = array_merge($tag_list, array_keys($child_list));
			$list = array_merge($list, $child_list);
			if ($limit and (count($list) >= $limit)) {
				$list = array_slice($list, 0, $limit);
				break;
			}
		}
		return $list;
	}

	public static function getDisciplineModel()
	{
		return (new Discipline);
	}

	public function getRecordLink()
	{
		return ModelHelper::getSearchParamLink('tag_list', [null => $this->name]);
	}

	public function getEditLink()
	{
		return Url::to(['/moderator/tag-edit', 'id' => $this->id]);
	}

	public function setEditing()
	{
		$this->scenario = static::SCENARIO_MODERATOR;
		if (!empty($this->discipline)) {
			$this->discipline_name = $this->discipline->name;
		} else {
			$this->discipline_id = -1;
		}
	}

}
