<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;


class DisciplineToChair extends ActiveRecord
{

	public static function tableName()
	{
		return 'discipline_to_chair';
	}

	public function rules()
	{
		return [
			[['discipline_id', 'chair_id'], 'unique', 'targetAttribute' => ['discipline_id', 'chair_id']],
			[['discipline_id', 'chair_id'], 'integer'],
		];
	}

	public static function primaryKey()
	{
		return [
			'discipline_id', 'chair_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'discipline_id' => Yii::t('app', 'Дисциплина'),
			'chair_id' => Yii::t('app', 'Кафедра'),
		];
	}

}
