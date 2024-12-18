<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;

class LessonType extends ActiveRecord
{

	public static function tableName()
	{
		return 'lesson_type';
	}

	public function rules()
	{
		return [
			[['id', 'name'], 'unique'],
			[['id'], 'integer'],
			[['name'], 'string', 'max' => 50],
		];
	}

	public static function primaryKey()
	{
		return [
			'id'
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => Yii::t('app', 'Запись'),
		];
	}

	public const TYPE_LECTION = 1;
	public const TYPE_PRACTICE = 2;
	public const TYPE_LAB = 3;

	public static function getList()
	{
		return self::find()
			->select(['name', 'id'])
			->indexBy('id')
			->column();
	}

	public static function getListByName()
	{
		return self::find()
			->select(['id', 'name'])
			->indexBy('name')
			->column();
	}
}
