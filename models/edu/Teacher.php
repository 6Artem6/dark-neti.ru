<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

use app\models\question\{Question};
use app\models\helpers\ModelHelper;


class Teacher extends ActiveRecord
{

	public static function tableName()
	{
		return 'teacher';
	}

	public function rules()
	{
		return [
			[['teacher_id', 'teacher_name'], 'unique', 'targetAttribute' => ['teacher_id', 'teacher_name']],
			[['teacher_id'], 'integer'],
			[['teacher_name'], 'string', 'min' => 2, 'max' => 50],
			[['teacher_fullname'], 'string', 'min' => 2, 'max' => 256],
			// [['is_checked'], 'boolean'],
		];
	}

	public static function primaryKey()
	{
		return [
			'teacher_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'teacher_id' => Yii::t('app', 'Преподаватель'),
			'teacher_fullname' => Yii::t('app', 'Преподаватель'),
		];
	}

	private int $_filter_question_count = 0;
	private int $_filter_question_helped_count = 0;

	public function getQuestions()
	{
		return $this->hasMany(Question::class, ['teacher_id' => 'teacher_id']);
	}

	public function getChair()
	{
		return $this->hasOne(Chair::class, ['chair_id' => 'chair_id'])
			->via('toChair');
	}

	public function getDisciplines()
	{
		return $this->hasMany(Discipline::class, ['discipline_id' => 'discipline_id'])
			->via('toDiscipline');
	}

	public function getGroups()
	{
		return $this->hasMany(StudentGroup::class, ['group_id' => 'group_id'])
			->via('toGroupToDiscipline');
	}

	public function getToChair()
	{
		return $this->hasMany(TeacherToChair::class, ['teacher_id' => 'teacher_id']);
	}

	public function getToDiscipline()
	{
		return $this->hasMany(TeacherToDiscipline::class, ['teacher_id' => 'teacher_id']);
	}

	public function getToGroupToDiscipline()
	{
		return $this->hasMany(TeacherToGroupToDiscipline::class, ['teacher_id' => 'teacher_id']);
	}

	public function getId()
	{
		return $this->teacher_id;
	}

	public function getName()
	{
		return $this->teacher_name;
	}

	public function getfilter_question_count()
	{
		return $this->_filter_question_count;
	}

	public function getfilter_question_helped_count()
	{
		return $this->_filter_question_helped_count;
	}

	public function setfilter_question_count(int $value)
	{
		$this->_filter_question_count = $value;
	}

	public function setfilter_question_helped_count(int $value)
	{
		$this->_filter_question_helped_count = $value;
	}

	public static function findById(int $id)
	{
		return self::find()->where(['teacher_id' => $id])->one();
	}

	public function getList()
	{
		return self::find()
			->select(['teacher_fullname'])
			->indexBy('teacher_id')
			->column();
	}

	public function getListByName(bool $add_all = false)
	{
		$result = [];
		$list = self::find()
			->select(['teacher_fullname'])
			->orderBy('teacher_fullname')
			->indexBy('teacher_fullname')
			->asArray()
			->all();
		$list = ArrayHelper::map($list, 'teacher_fullname', 'teacher_fullname');
		if ($add_all) {
			$result[null] = Yii::t('app', 'Все преподаватели');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

	public function getListWithQuestionsByName(bool $add_all = false)
	{
		$result = [];
		$list = self::find()
			->select(['teacher_fullname'])
			->distinct()
			->innerJoinWith('questions')
			->orderBy('teacher_fullname')
			->indexBy('teacher_fullname')
			->all();
		$list = ArrayHelper::map($list, 'teacher_fullname', 'teacher_fullname');
		if ($add_all) {
			$result[null] = Yii::t('app', 'Все преподаватели');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

	public function getRecordLink()
	{
		return ModelHelper::getSearchParamLink('teacher', $this->teacher_fullname);
	}

	public static function getRecordByName(string $teacher_fullname)
	{
		return self::findOne(['teacher_fullname' => $teacher_fullname]);
	}

	public static function getRecordChecked(int $teacher_id)
	{
		return self::findOne(['teacher_id' => $teacher_id, /*'is_checked' => true*/ ]);
	}

}
