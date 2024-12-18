<?php

namespace app\models\badge;

use Yii;
use yii\db\ActiveRecord;


class BadgeType extends ActiveRecord
{

	public static function tableName()
	{
		return 'badge_type';
	}

	public function rules()
	{
		return [
			[['!type_id'], 'unique'],
			[['type_id'], 'integer'],
			[['latin_name'], 'string', 'max' => 50],
			[['description'], 'string', 'max' => 1024],
		];
	}

	public static function primaryKey()
	{
		return [
			'type_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'type_id' => Yii::t('app', 'Тип достижения'),
			'latin_name' => Yii::t('app', 'Название на литинице'),
			'description' => Yii::t('app', 'Описание'),
		];
	}

	public const TYPE_HAS_ALL = 1;
	public const TYPE_IS_REGISTERED = 2;
	public const TYPE_TOP_YEAR = 3;
	public const TYPE_ANSWER = 4;
	public const TYPE_COMMENT = 5;
	public const TYPE_QUESTION = 6;
	public const TYPE_LIKE = 7;
	public const TYPE_TOP_MONTH = 8;
	public const TYPE_REPORT = 9;
	public const TYPE_SUBSCRIPTION = 10;

	public static function getLevelTypes()
	{
		return [
			static::TYPE_HAS_ALL,
			static::TYPE_IS_REGISTERED,
			static::TYPE_TOP_YEAR,
			static::TYPE_QUESTION,
			static::TYPE_ANSWER,
			static::TYPE_COMMENT,
			static::TYPE_LIKE,
			static::TYPE_TOP_MONTH,
			static::TYPE_REPORT,
			static::TYPE_SUBSCRIPTION,
		];
	}

}
