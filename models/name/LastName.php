<?php

namespace app\models\name;

use Yii;
use yii\db\ActiveRecord;

use app\models\user\User;


class LastName extends ActiveRecord
{

	public static function tableName()
	{
		return 'last_name';
	}

	public function rules()
	{
		return [
			[['id'], 'unique'],
			[['last_name'], 'string', 'max' => 50],
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
			'last_name' => Yii::t('app','Имя'),
			'is_taken' => Yii::t('app','Занято'),
		];
	}

	public static function getFreeFromFirstNameIds(string $first_name) {
		$last_names = User::find()->select('last_name')->where(['first_name' => $first_name])->column();
		return self::find()->select('id')->where(['NOT IN', 'last_name', $last_names])->column();
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