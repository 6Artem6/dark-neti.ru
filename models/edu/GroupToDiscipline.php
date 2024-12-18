<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;


class GroupToDiscipline extends ActiveRecord
{

	public static function tableName()
	{
		return 'group_to_discipline';
	}

	public function rules()
	{
		return [
			[['group_id', 'discipline_id'], 'unique', 'targetAttribute' => ['group_id', 'discipline_id']],
			[['group_id', 'discipline_id', 'year', 'semestr'], 'integer'],
		];
	}

	public static function primaryKey()
	{
		return [
			'group_id', 'discipline_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'group_id' => Yii::t('app', 'Группа'),
			'discipline_id' => Yii::t('app', 'Дисциплина'),
		];
	}

	public function getGroups()
	{
		return $this->hasMany(StudentGroup::class, ['group_id' => 'group_id']);
	}

}
