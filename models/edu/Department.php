<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;


class Department extends ActiveRecord
{

	public static function tableName()
	{
		return 'department';
	}

	public function rules()
	{
		return [
			[['department_id'], 'unique'],
			[['department_id'], 'integer'],
			[['department_name'], 'string'],
		];
	}

	public static function primaryKey()
	{
		return [
			'department_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'department_id' => Yii::t('app', 'Отделение'),
			'department_name' => Yii::t('app', 'Отделение'),
		];
	}

	public const TYPE_OCH = 1;
	public const TYPE_VECH = 2;
	public const TYPE_ZAOCH = 3;

	public function getList(bool $add_all = false)
	{
		$result = [];
		$list = self::find()
			->select(['department_name'])
			->orderBy('department_name')
			->indexBy('department_id')
			->column();
		if ($add_all) {
			$result[null] = Yii::t('app', 'Все отделения');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

	public function getListIndexByName()
	{
		return self::find()
			->select(['department_id', 'department_name'])
			->indexBy('department_name')
			->column();
	}

}
