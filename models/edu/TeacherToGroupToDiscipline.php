<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;


class TeacherToGroupToDiscipline extends ActiveRecord
{

	public static function tableName()
	{
		return 'teacher_to_group_to_discipline';
	}

	public function rules()
	{
		return [
			[['teacher_id', 'group_id', 'discipline_id'], 'unique', 'targetAttribute' => ['teacher_id', 'group_id', 'discipline_id']],
			[['teacher_id', 'group_id', 'discipline_id', 'year', 'semestr'], 'integer'],
		];
	}

	public static function primaryKey()
	{
		return [
			'teacher_id', 'group_id', 'discipline_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'teacher_id' => Yii::t('app', 'Преподаватель'),
			'group_id' => Yii::t('app', 'Группа'),
			'discipline_id' => Yii::t('app', 'Дисциплина'),
		];
	}

}
