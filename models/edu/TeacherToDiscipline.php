<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;


class TeacherToDiscipline extends ActiveRecord
{

	public static function tableName()
	{
		return 'teacher_to_discipline';
	}

	public function rules()
	{
		return [
			[['teacher_id', 'discipline_id'], 'unique', 'targetAttribute' => ['teacher_id', 'discipline_id']],
			[['teacher_id', 'discipline_id'], 'integer'],
		];
	}

	public static function primaryKey()
	{
		return [
			'teacher_id', 'discipline_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'teacher_id' => Yii::t('app', 'Преподаватель'),
			'discipline_id' => Yii::t('app', 'Дисциплина'),
		];
	}

}
