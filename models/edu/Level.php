<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;


class Level extends ActiveRecord
{

	public static function tableName()
	{
		return 'level';
	}

	public function rules()
	{
		return [
			[['level_id'], 'unique'],
			[['level_id'], 'integer'],
			[['level_name'], 'string'],
		];
	}

	public static function primaryKey()
	{
		return [
			'level_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'level_id' => Yii::t('app', 'Уровень образования'),
			'level_name' => Yii::t('app', 'Уровень образования'),
		];
	}

	public const TYPE_BAK = 1;
	public const TYPE_MAG = 2;
	public const TYPE_SPO = 3;

	public function getList(bool $add_all = false)
	{
		$result = [];
		$list = self::find()
			->select(['level_name'])
			->orderBy('level_name')
			->indexBy('level_id')
			->column();
		if ($add_all) {
			$result[null] = Yii::t('app', 'Все уровни образования');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

	public function getListIndexByName()
	{
		return self::find()
			->select(['level_id', 'level_name'])
			->indexBy('level_name')
			->column();
	}

}
