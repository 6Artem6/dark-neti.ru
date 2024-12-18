<?php

namespace app\models\register;

use Yii;
use yii\db\ActiveRecord;


class RegisterRejectReason extends ActiveRecord
{

	public static function tableName()
	{
		return 'register_reject_reason';
	}

	public function rules()
	{
		return [
			[['reason_id'], 'unique'],
			[['reason_id'], 'integer'],
			[['reason_text'], 'string', 'max' => 50],
		];
	}

	public static function primaryKey()
	{
		return [
			'reason_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'reason_id' => Yii::t('app', 'Код'),
			'reason_text' => Yii::t('app', 'Текст'),
		];
	}

	public const STATUS_NOT_FOUND = 1;
	public const STATUS_NOT_STUDENT = 2;
	public const STATUS_WRONG_FIO = 3;
	public const STATUS_WRONG_GROUP = 4;
	public const STATUS_WRONG_STUDENT_EMAIL = 5;
	public const STATUS_WRONG_EMAIL = 6;

}
