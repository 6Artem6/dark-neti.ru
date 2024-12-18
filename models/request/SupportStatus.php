<?php

namespace app\models\request;

use Yii;
use yii\db\ActiveRecord;


class SupportStatus extends ActiveRecord
{

	public static function tableName()
	{
		return 'support_status';
	}

	public function rules()
	{
		return [
			[['status_id'], 'unique'],
			[['status_id'], 'integer'],
			[['status_name'], 'string'],
		];
	}

	public static function primaryKey()
	{
		return [
			'status_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'status_id' => Yii::t('app','Тип статуса'),
			'status_name' => Yii::t('app','Тип статуса'),
		];
	}

	public const status_DONT_KNOW = 1;
	public const status_INPUT = 2;
	public const status_WISH = 3;
	public const status_UNEXPECTED = 4;
	public const status_ERROR = 5;
	public const status_LOGIN = 6;

	public function getList()
	{
		return self::find()->select(['status_name'])->indexBy('status_id')->column();
	}

}
