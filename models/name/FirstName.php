<?php

namespace app\models\name;

use Yii;
use yii\db\ActiveRecord;

use app\models\user\User;


class FirstName extends ActiveRecord
{

	public static function tableName()
	{
		return 'first_name';
	}

	public function rules()
	{
		return [
			[['id'], 'unique'],
			[['first_name'], 'string', 'max' => 50],
			[['is_taken'], 'boolean'],
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
			'id' => Yii::t('app','№ записи'),
			'first_name' => Yii::t('app','Имя'),
			'is_taken' => Yii::t('app','Занято'),
		];
	}

	public static function getAllIds() {
		return self::find()->select('id')->column();
	}

	public static function getFreeIds() {
		return self::find()->select('id')->where(['is_taken' => 0])->column();
	}

	public function setTaken() {
		if (!$this->is_taken) {
			$this->is_taken = true;
			$this->save();
		}
	}

}
