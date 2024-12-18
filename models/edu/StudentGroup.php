<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;

use app\models\user\UserData;


class StudentGroup extends ActiveRecord
{

	public static function tableName()
	{
		return 'student_group';
	}

	public function rules()
	{
		return [
			[['group_id', 'group_name'], 'unique'],
			[['group_id', 'faculty_id', 'department_id', 'level_id', 'course'], 'integer'],
			[['group_name'], 'string'],
			[['has_schedule'], 'boolean'],
		];
	}

	public static function primaryKey()
	{
		return [
			'group_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'group_id' => Yii::t('app', 'Группа'),
			'group_name' => Yii::t('app', 'Группа'),
		];
	}

	public function getFaculty()
	{
		return $this->hasOne(Faculty::class, ['faculty_id' => 'faculty_id']);
	}

	public function getDepartment()
	{
		return $this->hasOne(Department::class, ['department_id' => 'department_id']);
	}

	public function getLevel()
	{
		return $this->hasOne(Level::class, ['level_id' => 'level_id']);
	}

	public function getDisciplines()
	{
		return $this->hasMany(Discipline::class, ['discipline_id' => 'discipline_id'])
			->viaTable(GroupToDiscipline::tableName(), ['group_id' => 'group_id']);
	}

	public function getTeachers()
	{
		return $this->hasMany(Teacher::class, ['teacher_id' => 'teacher_id'])
			->viaTable(TeacherToGroupToDiscipline::tableName(), ['group_id' => 'group_id']);
	}

	public function getStudents()
	{
		return $this->hasMany(UserData::class, ['group_id' => 'group_id']);
	}

	public function getList(bool $add_all = false)
	{
		$result = [];
		$list = self::find()
			->select(['group_name'])
			->indexBy('group_id')
			->column();
		if ($add_all) {
			$result[null] = Yii::t('app', 'Все группы');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

	public static function getListWithSchedule()
	{
		return self::find()
			->select(['group_name'])
			->where(['has_schedule' => true])
			->indexBy('group_id')
			->column();
	}

	public static function getListRegistered()
	{
		return self::find()
			->select(['group_name'])
			->innerJoinWith('students')
			->where(['has_schedule' => true])
			->indexBy('group_id')
			->column();
	}

	public static function findByName(string $name)
	{
		return self::find()->where(['group_name' => $name])->one();
	}

}
