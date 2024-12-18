<?php

namespace app\models\request;

use Yii;
use yii\db\ActiveRecord;


class SupportType extends ActiveRecord
{

	public static function tableName()
	{
		return 'support_type';
	}

	public function rules()
	{
		return [
			[['type_id'], 'unique'],
			[['type_id'], 'integer'],
			[['type_name'], 'string'],
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
			'type_id' => Yii::t('app','Тип обращения'),
			'type_name' => Yii::t('app','Тип обращения'),
		];
	}

	public const TYPE_DONT_KNOW = 1;
	public const TYPE_INPUT = 2;
	public const TYPE_WISH = 3;
	public const TYPE_UNEXPECTED = 4;
	public const TYPE_ERROR = 5;
	public const TYPE_LOGIN = 6;

	public function getList()
	{
		return self::find()->select(['type_name'])->indexBy('type_id')->column();
	}

}
