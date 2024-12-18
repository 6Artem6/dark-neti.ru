<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;


class TeacherToChair extends ActiveRecord
{

	public static function tableName()
	{
		return 'teacher_to_chair';
	}

	public function rules()
	{
		return [
			[['teacher_id', 'chair_id'], 'unique', 'targetAttribute' => ['teacher_id', 'chair_id']],
			[['teacher_id', 'chair_id'], 'integer'],
		];
	}

	public static function primaryKey()
	{
		return [
			'teacher_id', 'chair_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'teacher_id' => Yii::t('app', 'Преподаватель'),
			'chair_id' => Yii::t('app', 'Кафедра'),
		];
	}

}
